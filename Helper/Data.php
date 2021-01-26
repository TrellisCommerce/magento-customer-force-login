<?php

namespace Trellis\CustomerForceLogin\Helper;

use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    private const XML_CONFIG_PATH_FORCE_LOGIN_ENABLED = 'trellis_forcelogin/general/enabled';
    private const XML_CONFIG_PATH_FORCE_LOGIN_ALLOWED_ACTIONNAMES = 'trellis_forcelogin/general/allowed_action_names';
    private const XML_CONFIG_PATH_FORCE_LOGIN_ALLOWED_CMS_PAGES = 'trellis_forcelogin/general/allowed_cms_pages';
    private const XML_CONFIG_PATH_STORECODE = 'web/url/use_store';

    /**
     * @var PageFactory
     */
    protected $cmsPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(Context $context, PageFactory $pageFactory)
    {
        parent::__construct($context);
        $this->cmsPageFactory = $pageFactory;
    }

    /**
     * Check if force login is enabled
     *
     * @param null $storeId
     * @return mixed
     */
    public function getIsForceLoginEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_FORCE_LOGIN_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get allowed action names
     *
     * @param null $storeId
     * @return array
     */
    public function getAllowedActionNames($storeId = null): array
    {
        $names = $this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_FORCE_LOGIN_ALLOWED_ACTIONNAMES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$names) {
            return [];
        }

        return explode(',', $names);
    }

    /**
     * Get allowed cms pages from config
     *
     * @param null $storeId
     * @return array
     */
    public function getAllowedCmsPageIdentifiers($storeId = null): array
    {
        $names = $this->scopeConfig->getValue(
            self::XML_CONFIG_PATH_FORCE_LOGIN_ALLOWED_CMS_PAGES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$names) {
            return [];
        }

        return explode(',', $names);
    }

    /**
     * Get a list of extra full action names
     * @param $actionName
     * @return bool
     */
    public function actionNameIsAllowed($actionName): bool
    {
        $allowedNames = $this->getAllowedActionNames();
        if (!$allowedNames) {
            return false;
        }
        return in_array($actionName, $allowedNames);
    }

    /**
     * Check if a cms page's access is blocked
     *
     * @param $pageId
     * @return bool
     */
    public function isValidCmsPage($pageId): bool
    {
        $allowedPageIdentifiers = $this->getAllowedCmsPageIdentifiers();
        if (!$allowedPageIdentifiers) {
            return false;
        }
        $cmsPage = $this->cmsPageFactory->create()->load($pageId);

        return in_array($cmsPage->getIdentifier(), $allowedPageIdentifiers);
    }

    /**
     * Check to see if storecode is configured to be shown in the url
     */
    public function storeCodeInUrl()
    {
        return $this->scopeConfig->isSetFlag(self::XML_CONFIG_PATH_STORECODE);
    }
}
