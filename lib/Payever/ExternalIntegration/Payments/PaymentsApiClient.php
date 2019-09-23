<?php
/**
 * Class represents Payever Payments API Connector
 *
 * PHP version 5.4
 *
 * @category  Payments
 * @package   Payever\Payments
 * @author    payever GmbH <service@payever.de>
 * @copyright 2017-2019 payever GmbH
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://getpayever.com/developer/api-documentation/ Documentation
 */

namespace Payever\ExternalIntegration\Payments;

use Payever\ExternalIntegration\Core\Authorization\OauthToken;
use Payever\ExternalIntegration\Core\CommonApiClient;
use Payever\ExternalIntegration\Core\Http\RequestBuilder;
use Payever\ExternalIntegration\Payments\Base\PaymentsApiClientInterface;
use Payever\ExternalIntegration\Payments\Http\RequestEntity\AuthorizePaymentRequest;
use Payever\ExternalIntegration\Payments\Http\RequestEntity\CreatePaymentRequest;
use Payever\ExternalIntegration\Payments\Http\RequestEntity\ListPaymentsRequest;
use Payever\ExternalIntegration\Payments\Http\RequestEntity\RefundPaymentRequest;
use Payever\ExternalIntegration\Payments\Http\RequestEntity\ShippingGoodsPaymentRequest;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\AuthorizePaymentResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\CancelPaymentResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\CollectPaymentsResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\CreatePaymentResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\GetTransactionResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\LatePaymentsResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\ListPaymentOptionsResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\ListPaymentsResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\RefundPaymentResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\RemindPaymentResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\RetrieveApiCallResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\RetrievePaymentResponse;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\ShippingGoodsPaymentResponse;

/**
 * Class represents Payever Payments API Connector
 *
 * PHP version 5.4
 *
 * @category  Payments
 * @package   Payever\Payments
 * @author    Andrey Puhovsky <a.puhovsky@gmail.com>
 * @copyright 2017-2019 payever GmbH
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://getpayever.com/developer/api-documentation/ Documentation
 */
class PaymentsApiClient extends CommonApiClient implements PaymentsApiClientInterface
{
    const SUB_URL_CREATE_PAYMENT         = 'api/payment';
    const SUB_URL_RETRIEVE_PAYMENT       = 'api/payment/%s';
    const SUB_URL_LIST_PAYMENTS          = 'api/payment';
    const SUB_URL_REFUND_PAYMENT         = 'api/payment/refund/%s';
    const SUB_URL_AUTHORIZE_PAYMENT      = 'api/payment/authorize/%s';
    const SUB_URL_REMIND_PAYMENT         = 'api/payment/remind/%s';
    const SUB_URL_COLLECT_PAYMENTS       = 'api/payment/collect/%s';
    const SUB_URL_LATE_PAYMENTS          = 'api/payment/late-payment/%s';
    const SUB_URL_SHIPPING_GOODS_PAYMENT = 'api/payment/shipping-goods/%s';
    const SUB_URL_CANCEL_PAYMENT         = 'api/payment/cancel/%s';
    const SUB_URL_RETRIEVE_API_CALL      = 'api/%s';
    const SUB_URL_LIST_PAYMENT_OPTIONS   = 'api/shop/%s/payment-options/%s';
    const SUB_URL_TRANSACTION            = 'api/rest/v1/transactions/%s';

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function createPaymentRequest(CreatePaymentRequest $createPaymentRequest)
    {
        $this->configuration->assertLoaded();

        if (!$createPaymentRequest->getChannel()) {
            $createPaymentRequest->setChannel(
                $this->configuration->getChannelSet()
            );
        }

        $request = RequestBuilder::post($this->getCreatePaymentURL())
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_CREATE_PAYMENT)->getAuthorizationString()
            )
            ->setRequestEntity($createPaymentRequest)
            ->setResponseEntity(new CreatePaymentResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function retrievePaymentRequest($paymentId)
    {
        $this->configuration->assertLoaded();

        $request = RequestBuilder::get($this->getRetrievePaymentURL($paymentId))
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_PAYMENT_INFO)->getAuthorizationString()
            )
            ->setResponseEntity(new RetrievePaymentResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function listPaymentsRequest(ListPaymentsRequest $listPaymentsRequest)
    {
        $this->configuration->assertLoaded();

        $request = RequestBuilder::get($this->getListPaymentsURL())
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_PAYMENT_ACTIONS)->getAuthorizationString()
            )
            ->setRequestEntity($listPaymentsRequest)
            ->setResponseEntity(new ListPaymentsResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function refundPaymentRequest($paymentId, $amount)
    {
        $this->configuration->assertLoaded();

        $request = RequestBuilder::post($this->getRefundPaymentURL($paymentId))
            ->setParams(
                array(
                    'amount' => $amount,
                )
            )
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_PAYMENT_ACTIONS)->getAuthorizationString()
            )
            ->setRequestEntity(new RefundPaymentRequest())
            ->setResponseEntity(new RefundPaymentResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function authorizePaymentRequest($paymentId, AuthorizePaymentRequest $authorizePaymentRequest = null)
    {
        $this->configuration->assertLoaded();

        $request = RequestBuilder::post($this->getAuthorizePaymentURL($paymentId))
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_PAYMENT_ACTIONS)->getAuthorizationString()
            )
            ->setRequestEntity($authorizePaymentRequest ?: new AuthorizePaymentRequest())
            ->setResponseEntity(new AuthorizePaymentResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated This request is only available for Santa DE Invoice and not used anywhere
     *
     * @throws \Exception
     */
    public function remindPaymentRequest($paymentId)
    {
        $this->configuration->assertLoaded();

        $request = RequestBuilder::post($this->getRemindPaymentURL($paymentId))
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_PAYMENT_ACTIONS)->getAuthorizationString()
            )
            ->setResponseEntity(new RemindPaymentResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated This request is only available for Santa DE Invoice and not used anywhere
     *
     * @throws \Exception
     */
    public function collectPaymentsRequest($paymentId)
    {
        $this->configuration->assertLoaded();

        $request = RequestBuilder::post($this->getCollectPaymentsURL($paymentId))
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_PAYMENT_ACTIONS)->getAuthorizationString()
            )
            ->setResponseEntity(new CollectPaymentsResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated This request is only available for Santa DE Invoice and not used anywhere
     *
     * @throws \Exception
     */
    public function latePaymentsRequest($paymentId)
    {
        $this->configuration->assertLoaded();

        $request = RequestBuilder::post($this->getLatePaymentsURL($paymentId))
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_PAYMENT_ACTIONS)->getAuthorizationString()
            )
            ->setResponseEntity(new LatePaymentsResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function shippingGoodsPaymentRequest(
        $paymentId,
        ShippingGoodsPaymentRequest $shippingGoodsPaymentRequest = null
    ) {
        $this->configuration->assertLoaded();

        $request = RequestBuilder::post($this->getShippingGoodsPaymentURL($paymentId))
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_PAYMENT_ACTIONS)->getAuthorizationString()
            )
            ->setRequestEntity($shippingGoodsPaymentRequest ?: new ShippingGoodsPaymentRequest())
            ->setResponseEntity(new ShippingGoodsPaymentResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function cancelPaymentRequest($paymentId)
    {
        $this->configuration->assertLoaded();

        $request = RequestBuilder::post($this->getCancelPaymentURL($paymentId))
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_PAYMENT_ACTIONS)->getAuthorizationString()
            )
            ->setResponseEntity(new CancelPaymentResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function retrieveApiCallRequest($callId)
    {
        $this->configuration->assertLoaded();

        $request = RequestBuilder::get($this->getRetrieveApiCallURL($callId))
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_PAYMENT_ACTIONS)->getAuthorizationString()
            )
            ->setResponseEntity(new RetrieveApiCallResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function listPaymentOptionsRequest($params = array(), $businessUuid = '', $channel = '')
    {
        $businessUuid = $businessUuid ?: $this->getConfiguration()->getBusinessUuid();
        $channel = $channel ?: $this->getConfiguration()->getChannelSet();

        $request = RequestBuilder::get($this->getListPaymentOptionsURL($businessUuid, $channel, $params))
            ->setResponseEntity(new ListPaymentOptionsResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function getTransactionRequest($paymentId)
    {
        $this->configuration->assertLoaded();

        $request = RequestBuilder::get($this->getTransactionURL($paymentId))
            ->addRawHeader(
                $this->getToken(OauthToken::SCOPE_PAYMENT_ACTIONS)->getAuthorizationString()
            )
            ->setResponseEntity(new GetTransactionResponse())
            ->build()
        ;

        $response = $this->getHttpClient()->execute($request);

        return $response;
    }

    /**
     * Returns URL for Create Payment path
     *
     * @return string
     */
    protected function getCreatePaymentURL()
    {
        return $this->getBaseUrl() . self::SUB_URL_CREATE_PAYMENT;
    }

    /**
     * Returns URL for Retrieve Payment path
     *
     * @param string $paymentId
     *
     * @return string
     */
    protected function getRetrievePaymentURL($paymentId)
    {
        return $this->getBaseUrl() . sprintf(self::SUB_URL_RETRIEVE_PAYMENT, $paymentId);
    }

    /**
     * Returns URL for List Payments path
     *
     * @return string
     */
    protected function getListPaymentsURL()
    {
        return $this->getBaseUrl() . self::SUB_URL_LIST_PAYMENTS;
    }

    /**
     * Returns URL for Refund Payment path
     *
     * @param string $paymentId
     *
     * @return string
     */
    protected function getRefundPaymentURL($paymentId)
    {
        return $this->getBaseUrl() . sprintf(self::SUB_URL_REFUND_PAYMENT, $paymentId);
    }

    /**
     * Returns URL for Authorize Payment path
     *
     * @param string $paymentId
     *
     * @return string
     */
    protected function getAuthorizePaymentURL($paymentId)
    {
        return $this->getBaseUrl() . sprintf(self::SUB_URL_AUTHORIZE_PAYMENT, $paymentId);
    }

    /**
     * Returns URL for Remind Payment path
     *
     * @param string $paymentId
     *
     * @return string
     */
    protected function getRemindPaymentURL($paymentId)
    {
        return $this->getBaseUrl() . sprintf(self::SUB_URL_REMIND_PAYMENT, $paymentId);
    }

    /**
     * Returns URL for Collect Payment path
     *
     * @param string $paymentId
     *
     * @return string
     */
    protected function getCollectPaymentsURL($paymentId)
    {
        return $this->getBaseUrl() . sprintf(self::SUB_URL_COLLECT_PAYMENTS, $paymentId);
    }

    /**
     * Returns URL for Late Payments path
     *
     * @param string $paymentId
     *
     * @return string
     */
    protected function getLatePaymentsURL($paymentId)
    {
        return $this->getBaseUrl() . sprintf(self::SUB_URL_LATE_PAYMENTS, $paymentId);
    }

    /**
     * Returns URL for Shipping Goods Payment path
     *
     * @param string $paymentId
     *
     * @return string
     */
    protected function getShippingGoodsPaymentURL($paymentId)
    {
        return $this->getBaseUrl() . sprintf(self::SUB_URL_SHIPPING_GOODS_PAYMENT, $paymentId);
    }

    /**
     * Returns URL for Cancel Payment path
     *
     * @param string $paymentId
     *
     * @return string
     */
    protected function getCancelPaymentURL($paymentId)
    {
        return $this->getBaseUrl() . sprintf(self::SUB_URL_CANCEL_PAYMENT, $paymentId);
    }

    /**
     * Returns URL for Retrieve API Call path
     *
     * @param string $callId
     *
     * @return string
     */
    protected function getRetrieveApiCallURL($callId)
    {
        return $this->getBaseUrl() . sprintf(self::SUB_URL_RETRIEVE_API_CALL, $callId);
    }

    /**
     * Returns URL for Available Payment Options path
     *
     * @param string $businessUuid
     * @param string $channel
     * @param string $params
     *
     * @return string
     */
    protected function getListPaymentOptionsURL($businessUuid, $channel, $params = array())
    {
        return $this->getBaseUrl() . sprintf(self::SUB_URL_LIST_PAYMENT_OPTIONS, $businessUuid, $channel)
            . (empty($params) ? '' : '?' . http_build_query($params));
    }

    /**
     * Returns URL to Transaction path
     *
     * @param int $paymentId
     *
     * @return string
     */
    protected function getTransactionURL($paymentId)
    {
        return $this->getBaseUrl() . sprintf(self::SUB_URL_TRANSACTION, $paymentId);
    }
}
