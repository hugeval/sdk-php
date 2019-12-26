<?php
/**
 * PHP version 5.4 and 7
 *
 * @package   Payever\ThirdParty
 * @author    Hennadii.Shymanskyi <gendosua@gmail.com>
 * @copyright 2017-2019 payever GmbH
 * @license   MIT <https://opensource.org/licenses/MIT>
 */

namespace Payever\ExternalIntegration\ThirdParty\Action;

use Payever\ExternalIntegration\Inventory\Http\RequestEntity\InventoryChangedRequestEntity;
use Payever\ExternalIntegration\Inventory\Http\RequestEntity\InventoryCreateRequestEntity;
use Payever\ExternalIntegration\Inventory\InventoryApiClient;
use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRemovedRequestEntity;
use Payever\ExternalIntegration\Products\Http\RequestEntity\ProductRequestEntity;
use Payever\ExternalIntegration\Products\ProductsApiClient;
use Payever\ExternalIntegration\ThirdParty\Enum\ActionEnum;
use Psr\Log\LoggerInterface;

class OutwardActionProcessor
{
    /** @var ProductsApiClient */
    private $productsApiClient;

    /** @var InventoryApiClient */
    private $inventoryApiClient;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ProductsApiClient $productsApiClient,
        InventoryApiClient $inventoryApiClient,
        LoggerInterface $logger
    ) {
        $this->productsApiClient = $productsApiClient;
        $this->inventoryApiClient = $inventoryApiClient;
        $this->logger = $logger;
    }

    /**
     * @param string $action - {@see ActionEnum}
     * @param InventoryChangedRequestEntity|ProductRequestEntity|ProductRemovedRequestEntity|array|string $payload
     *
     * @throws \RuntimeException - when given action is unknown
     * @throws \Exception - bubbles up anything thrown by API
     */
    public function process($action, $payload)
    {
        $loggerPrefix = '[OUTWARD_ACTION_REQUEST]';

        $this->logger->info(
            sprintf('%s Processing action request', $loggerPrefix),
            compact('action')
        );

        $this->logger->debug(
            sprintf('%s Action request payload', $loggerPrefix),
            compact($payload)
        );

        try {
            $this->executeActionRequest($action, $payload);
        } catch (\Exception $exception) {
            $this->logger->critical(
                sprintf(
                    '%s Processing action failed. EXCEPTION: %s: %s',
                    $loggerPrefix,
                    $exception->getCode(),
                    $exception->getMessage()
                ),
                compact('action', 'payload')
            );

            throw $exception;
        }
    }

    /**
     * @param string $action - {@see ActionEnum}
     * @param InventoryChangedRequestEntity|ProductRequestEntity|ProductRemovedRequestEntity|array|string $payload
     *
     * @throws \Exception
     */
    private function executeActionRequest($action, $payload)
    {
        switch ($action) {
            case ActionEnum::ACTION_SET_INVENTORY:
                $this->inventoryApiClient->createInventory(
                    $payload instanceof InventoryCreateRequestEntity ? $payload : new InventoryCreateRequestEntity($payload)
                );
                break;
            case ActionEnum::ACTION_ADD_INVENTORY:
                $this->inventoryApiClient->addInventory(
                    $payload instanceof InventoryChangedRequestEntity ? $payload : new InventoryChangedRequestEntity($payload)
                );
                break;
            case ActionEnum::ACTION_SUBTRACT_INVENTORY:
                $this->inventoryApiClient->subtractInventory(
                    $payload instanceof InventoryChangedRequestEntity ? $payload : new InventoryChangedRequestEntity($payload)
                );
                break;
            case ActionEnum::ACTION_CREATE_PRODUCT:
                $this->productsApiClient->createProduct(
                    $payload instanceof ProductRequestEntity ? $payload : new ProductRequestEntity($payload)
                );
                break;
            case ActionEnum::ACTION_UPDATE_PRODUCT:
                $this->productsApiClient->updateProduct(
                    $payload instanceof ProductRequestEntity ? $payload : new ProductRequestEntity($payload)
                );
                break;
            case ActionEnum::ACTION_REMOVE_PRODUCT:
                $this->productsApiClient->removeProduct(
                    $payload instanceof ProductRemovedRequestEntity ? $payload : new ProductRemovedRequestEntity($payload)
                );
                break;
            default:
                throw new \RuntimeException(sprintf('Unknown action %s', $action));
        }
    }
}
