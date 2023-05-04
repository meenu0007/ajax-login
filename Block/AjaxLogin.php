<?php
/**
 * Meenu0007 Module for Ajax Login Block.
 *
 * @category  Meenu0007 Module
 * @package   Meenu0007_AjaxLogin
 * @author    Meenu Thomas
 * @copyright Copyright (c)
 */
declare(strict_types=1);

namespace Meenu0007\AjaxLogin\Block;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

class AjaxLogin extends \Magento\Framework\View\Element\Template
{

    /**
     * Query param name for last url visited
     */
    const REFERER_QUERY_PARAM_NAME = 'referer';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Url\HostChecker
     */
    private $hostChecker;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    private $urlDecoder;

    /**
     * @var Context
     */
    protected $httpContext;

    /**
     * @var Registry
     */
    protected $registry;
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param \Magento\Framework\Url\HostChecker|null $hostChecker
     * @param \Magento\Framework\Url\DecoderInterface|null $urlDecoder
     * @param Context $httpContext
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        \Magento\Framework\Url\HostChecker $hostChecker = null,
        \Magento\Framework\Url\DecoderInterface $urlDecoder = null,
        Context $httpContext,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->hostChecker = $hostChecker ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Url\HostChecker::class);
        $this->urlDecoder = $urlDecoder ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Url\DecoderInterface::class);
        $this->httpContext = $httpContext;
        $this->_registry = $registry;
    }

    /**
     * @return string
     */
    public function getPostActionUrl()
    {
        $params = [];
        $referer = $this->getRequestReferrer();
        if ($referer) {
            $params = [
                self::REFERER_QUERY_PARAM_NAME => $referer,
            ];
        }
        return $this->urlBuilder->getUrl('customer/ajax/login', $params);
    }
    /**
     * Retrieve url of forgot password page
     *
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->urlBuilder->getUrl('customer/account/forgotpassword');
    }
    /**
     * Check if autocomplete is disabled on storefront
     *
     * @return bool
     */
    public function isAutocompleteDisabled()
    {
        return (bool)!$this->_scopeConfig->getValue(
            \Magento\Customer\Model\Form::XML_PATH_ENABLE_AUTOCOMPLETE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return mixed|null
     */
    private function getRequestReferrer()
    {
        $referer = $this->request->getParam(self::REFERER_QUERY_PARAM_NAME);
        if ($referer && $this->hostChecker->isOwnOrigin($this->urlDecoder->decode($referer))) {
            return $referer;
        }
        return null;
    }
    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function customerIsAlreadyLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    public function isLoginRequiredToBuy()
    {
        $skus= ["sskcb"];
        $returnVal = false;
        $currProductSku = $this->_registry->registry('current_product')->getSku();
        if (in_array($currProductSku, $skus)) {
            $returnVal = true;
        } else {
            $returnVal = false;
        }
        return $returnVal;
    }
}
