<?php

/*
 * This file is part of the "AsyntaiChatbotBundle" for Kimai.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\AsyntaiChatbotBundle\Controller;

use App\Controller\AbstractController;
use App\Utils\PageSetup;
use KimaiPlugin\AsyntaiChatbotBundle\Service\AsyntaiSettings;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Settings page: connect the Asyntai account and choose who sees the chat.
 *
 * Every write is a POST form with a CSRF token. The connect window only returns
 * the site ID; the page script puts it into the hidden form and submits it.
 */
#[Route(path: '/admin/asyntai')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[IsGranted('system_configuration')]
final class AsyntaiController extends AbstractController
{
    private const CSRF_ID = 'asyntai_chatbot';

    public function __construct(private readonly AsyntaiSettings $settings)
    {
    }

    #[Route(path: '', name: 'asyntai_chatbot', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('@AsyntaiChatbot/index.html.twig', [
            'page_setup' => new PageSetup('asyntai.title'),
            'site_id' => $this->settings->getSiteId(),
            'account_email' => $this->settings->getAccountEmail(),
            'show_users' => $this->settings->showToUsers(),
            'show_login' => $this->settings->showOnLogin(),
            'origin' => $this->settings->getOrigin(),
            'csrf_id' => self::CSRF_ID,
        ]);
    }

    #[Route(path: '/connect', name: 'asyntai_chatbot_connect', methods: ['POST'])]
    public function connect(Request $request): RedirectResponse
    {
        if (!$this->hasValidToken($request)) {
            return $this->back();
        }

        $siteId = trim($request->request->getString('site_id'));
        if (!AsyntaiSettings::isValidSiteId($siteId)) {
            $this->flashError('asyntai.connect_invalid');

            return $this->back();
        }

        $email = trim($request->request->getString('account_email'));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $email = '';
        }

        $this->settings->saveConnection(
            $siteId,
            $this->settings->cleanScriptUrl($request->request->getString('script_url')),
            $email
        );
        $this->flashSuccess('asyntai.connect_done');

        return $this->back();
    }

    #[Route(path: '/display', name: 'asyntai_chatbot_display', methods: ['POST'])]
    public function display(Request $request): RedirectResponse
    {
        if (!$this->hasValidToken($request)) {
            return $this->back();
        }

        $this->settings->saveDisplay($request->request->has('show_users'), $request->request->has('show_login'));
        $this->flashSuccess('asyntai.display_saved');

        return $this->back();
    }

    #[Route(path: '/reset', name: 'asyntai_chatbot_reset', methods: ['POST'])]
    public function reset(Request $request): RedirectResponse
    {
        if (!$this->hasValidToken($request)) {
            return $this->back();
        }

        $this->settings->saveConnection('', '', '');
        $this->flashSuccess('asyntai.reset_done');

        return $this->back();
    }

    private function hasValidToken(Request $request): bool
    {
        if ($this->isCsrfTokenValid(self::CSRF_ID, $request->request->getString('_token'))) {
            return true;
        }
        $this->flashError('action.csrf.error');

        return false;
    }

    private function back(): RedirectResponse
    {
        return $this->redirectToRoute('asyntai_chatbot');
    }
}
