<?php
namespace Sto\Tellmatic\Utility;

use Tx\Authcode\Domain\Model\AuthCode;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\View\TemplateView;

class MailUtility implements SingletonInterface
{
    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
        $this->configurationManager = $configurationManager;
        $this->settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
    }

    /**
     * Usees the tinyurls extension to generate
     *
     * @param string $action
     * @param string $authCode
     * @return string
     */
    public function generateTinyUrl($action, $authCode) {

        if (empty($this->settings['authCodeUrlSpeaking'])) {
            return NULL;
        }

        if (empty($this->settings['authCodeUrlTempate'][$action])) {
            return NULL;
        }

        $authCodeUrl['value'] = $this->settings['authCodeUrlTempate'][$action];
        $authCodeUrl['insertData'] = 1;
        $authCodeUrl = $this->configurationManager->getContentObject()->cObjGetSingle('TEXT', $authCodeUrl);

        if (empty($authCodeUrl)) {
            return NULL;
        }

        $authCodeUrl = str_replace('###authcode###', $authCode, $authCodeUrl);

        return $authCodeUrl;
    }

    /**
     * Returns a template view instance that can be used for email generation.
     *
     * @param string $mailTemplate
     * @param string $templatePath
     * @param TemplateView $view
     * @return TemplateView
     */
    public function getMailView($mailTemplate, $templatePath, $view)
    {
        /** @var TemplateView $view */
        $view = clone($view);
        $view->setTemplatePathAndFilename(MailUtility::getMailTemplatePath($templatePath) . $mailTemplate . '.txt');
        return $view;
    }

    /**
     * Reads the mail template path from the settings.
     *
     * @param string $path
     * @return string
     */
    public function getMailTemplatePath($path)
    {
        $templatePath = GeneralUtility::getFileAbsFileName($path);
        if (empty($templatePath)) {
            throw new \InvalidArgumentException('The configured mail tempalte directory is invalid: ' . $this->settings['mail']['templatePath']);
        }
        return rtrim($templatePath, '/') . '/';
    }

    /**
     * Generates an action URI with the given auth code.
     *
     * If the action is "updateForm" an additional unsubscribe URI will be generated.
     *
     * These variables are assigned in the view:
     * actionUrl - contains the URI to the given action.
     * unsubscribeUrl - only if $buildUnsubscribeUrl is TRUE.
     *
     * @param string $action
     * @param AuthCode $authCode
     * @param string $subject
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     * @param bool $buildUnsubscribeUrl
     * @param \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder
     */
    public function sendAuthCodeMail($action, $authCode, $subject, $view, $uriBuilder, $buildUnsubscribeUrl = false)
    {

        $email = $authCode->getIdentifier();
        $view->assign('email', $email);

        $actionUrl = $uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(true)
            ->setUseCacheHash(false)
            ->uriFor($action, array('authCode' => $authCode->getAuthCode()));
        $actionUrlTiny = $this->generateTinyUrl($action, $authCode->getAuthCode());
        $view->assign('actionUrl', $actionUrlTiny ? $actionUrlTiny : $actionUrl);

        if ($buildUnsubscribeUrl) {
            $unsubscribeUrl = $uriBuilder
                ->reset()
                ->setCreateAbsoluteUri(true)
                ->setUseCacheHash(false)
                ->uriFor('unsubscribeForm', array('authCode' => $authCode->getAuthCode()));
            $unsubscribeUrlTiny = $this->generateTinyUrl('unsubscribeForm', $authCode->getAuthCode());
            $view->assign('unsubscribeUrl', $unsubscribeUrlTiny ? $unsubscribeUrlTiny : $unsubscribeUrl);
        }

        $mailtext = $view->render();

        $this->sendMail($email, $subject, $mailtext);
    }

    /**
     * Sends an email with the given parameters.
     *
     * @param string $email
     * @param string $subject
     * @param string $mailtext
     */
    public function sendMail($email, $subject, $mailtext)
    {
        $mail = $this->objectManager->get(MailMessage::class);
        $fromName = !empty($this->settings['mail']['fromName']) ? $this->settings['mail']['fromName'] : null;
        $mail->setFrom($this->settings['mail']['from'], $fromName);
        $mail->setTo($email);
        $mail->setSubject($subject);
        $mail->addPart($mailtext);
        $mail->send();
    }
}