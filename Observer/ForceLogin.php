<?php

declare(strict_types=1);

namespace Trellis\CustomerForceLogin\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Trellis\CustomerForceLogin\Helper\Data;

class ForceLogin implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var Data
     */
    protected $loginHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Session               $customerSession
     * @param RedirectInterface     $redirect
     * @param Data                  $loginHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $customerSession,
        RedirectInterface $redirect,
        Data $loginHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->loginHelper = $loginHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Redirect customers to login page if not already logged in
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->customerSession->isLoggedIn() || !$this->loginHelper->getIsForceLoginEnabled()) {
            return;
        }

        $request = $observer->getEvent()->getRequest();
        if ($this->isAllowedCmsPage($request)) {
            return;
        }

        if ($this->isAllowedAction($request)) {
            return;
        }

        $controller = $observer->getControllerAction();
        $response = $controller->getResponse();
        $response->setNoCacheHeaders();
        $this->redirect->redirect($response, 'customer/account/login');
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function isAllowedCmsPage(RequestInterface $request): bool
    {
        $cmsPageId = $request->getParam('page_id');
        $fullActionName = $request->getFullActionName();
        if ($fullActionName === 'cms_index_index' && $this->loginHelper->isValidCmsPage('home')) {
            return true;
        }

        // Let configured CMS pages always be viewed
        if ($fullActionName === 'cms_page_view' && $this->loginHelper->isValidCmsPage($cmsPageId)) {
            return true;
        }

        return false;
    }

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    private function isAllowedAction(RequestInterface $request): bool
    {
        $fullActionName = $request->getFullActionName();
        $controller = $request->getControllerName();
        $moduleName = $request->getModuleName();

        return $controller === 'directpost_payment' || in_array($moduleName, ['authorizenet', 'customer']
            ) || $this->loginHelper->actionNameIsAllowed($fullActionName);
    }

    /**
     * Get store secure base url
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB, true);
        if ($this->loginHelper->storeCodeInUrl()) {
            $baseUrl .= $this->storeManager->getStore()->getCode() . '/';
        }

        return $baseUrl;
    }

}
