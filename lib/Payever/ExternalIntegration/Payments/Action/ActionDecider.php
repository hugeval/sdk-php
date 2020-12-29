<?php
/**
 * This class represents payment actions used in Payever API
 *
 * PHP version 5.4
 *
 * @package   Payever\Payments
 * @author    payever GmbH <service@payever.de>
 * @copyright 2017-2019 payever GmbH
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://getpayever.com/developer/api-documentation/ Documentation
 */

namespace Payever\ExternalIntegration\Payments\Action;

use Payever\ExternalIntegration\Payments\Base\PaymentsApiClientInterface;
use Payever\ExternalIntegration\Payments\Http\MessageEntity\GetTransactionResultEntity;
use Payever\ExternalIntegration\Payments\Http\ResponseEntity\GetTransactionResponse;

/**
 * This class represents payment actions used in Payever API
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
class ActionDecider implements ActionDeciderInterface
{
    /** @var PaymentsApiClientInterface */
    protected $api;

    /**
     * AbstractActionDecider constructor.
     *
     * @param PaymentsApiClientInterface $api
     */
    public function __construct(PaymentsApiClientInterface $api)
    {
        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function isActionAllowed($paymentId, $transactionAction, $throwException = true)
    {
        $this->assertArguments($transactionAction, $paymentId);
        if (in_array($this->getPolyfillAction($transactionAction), $this->getEnabledActions($paymentId), true)) {
            return true;
        }

        return $this->assertFound($throwException, $transactionAction, $paymentId);
    }

    /**
     * Check if the cancel action for the transaction is allowed
     *
     * @param string $paymentId
     * @param bool $throwException
     *
     * @return bool
     *
     * @throws \Exception when $throwException is true and target action is not allowed
     */
    public function isCancelAllowed($paymentId, $throwException = true)
    {
        return $this->isActionAllowed($paymentId, static::ACTION_CANCEL, $throwException);
    }

    /**
     * Check if the refund action for the transaction is allowed
     *
     * @param string $paymentId
     * @param bool $throwException
     *
     * @return bool
     *
     * @throws \Exception when $throwException is true and target action is not allowed
     */
    public function isRefundAllowed($paymentId, $throwException = true)
    {
        return $this->isActionAllowed($paymentId, static::ACTION_REFUND, $throwException);
    }

    /**
     * Check if the shipping goods action for the transaction is allowed
     *
     * @param string $paymentId
     * @param bool $throwException
     *
     * @return bool
     *
     * @throws \Exception when $throwException is true and target action is not allowed
     */
    public function isShippingAllowed($paymentId, $throwException = true)
    {
        return $this->isActionAllowed($paymentId, static::ACTION_SHIPPING_GOODS, $throwException);
    }

    /**
     * @param string $paymentId
     * @return string[]
     */
    protected function getEnabledActions($paymentId)
    {
        $getTransactionResponse = $this->api->getTransactionRequest($paymentId);
        /** @var GetTransactionResponse $getTransactionEntity */
        $getTransactionEntity = $getTransactionResponse->getResponseEntity();
        /** @var GetTransactionResultEntity $getTransactionResult */
        $getTransactionResult = $getTransactionEntity->getResult();
        $actions = $getTransactionResult->getActions();
        $enabledActions = [];
        foreach ($actions as $action) {
            if (!is_object($action) || !isset($action->action, $action->enabled) || !(bool) $action->enabled) {
                continue;
            }
            $enabledActions[] = $this->getPolyfillAction($action->action);
        }

        return $enabledActions;
    }

    /**
     * @param string $action
     * @return string
     */
    protected function getPolyfillAction($action)
    {
        if ($action === ActionDeciderInterface::ACTION_RETURN) {
            $action = ActionDeciderInterface::ACTION_REFUND;
        }

        return $action;
    }

    /**
     * @param string|null $transactionAction
     * @param string|null $paymentId
     * @throws \Exception
     */
    protected function assertArguments($transactionAction, $paymentId)
    {
        if (empty($transactionAction) || empty($paymentId)) {
            throw new \Exception('Wrong arguments.');
        }
    }

    /**
     * @param bool $throwException
     * @param string $transactionAction
     * @param string $paymentId
     * @return bool
     * @throws ActionNotAllowedException
     */
    protected function assertFound($throwException, $transactionAction, $paymentId)
    {
        if ($throwException) {
            $message = sprintf(
                'Action "%s" is not allowed for payment id "%s"',
                $transactionAction,
                $paymentId
            );

            throw new ActionNotAllowedException($message);
        }

        return false;
    }
}
