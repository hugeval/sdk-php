<?php
/**
 * PHP version 5.4 and 7
 *
 * @package   Payever\Plugins
 * @author    Hennadii.Shymanskyi <gendosua@gmail.com>
 * @copyright 2017-2019 payever GmbH
 * @license   MIT <https://opensource.org/licenses/MIT>
 */

namespace Payever\ExternalIntegration\Plugins;

use Payever\ExternalIntegration\Core\Authorization\OauthTokenList;
use Payever\ExternalIntegration\Core\Base\ClientConfigurationInterface;
use Payever\ExternalIntegration\Core\Base\HttpClientInterface;
use Payever\ExternalIntegration\Core\CommonApiClient;
use Payever\ExternalIntegration\Core\Http\RequestBuilder;
use Payever\ExternalIntegration\Core\Http\RequestEntity;
use Payever\ExternalIntegration\Core\Http\Response;
use Payever\ExternalIntegration\Core\Http\ResponseEntity;
use Payever\ExternalIntegration\Plugins\Base\PluginRegistryInfoProviderInterface;
use Payever\ExternalIntegration\Plugins\Base\PluginsApiClientInterface;
use Payever\ExternalIntegration\Plugins\Http\RequestEntity\PluginRegistryRequestEntity;
use Payever\ExternalIntegration\Plugins\Http\ResponseEntity\CommandsResponseEntity;
use Payever\ExternalIntegration\Plugins\Http\ResponseEntity\PluginRegistryResponseEntity;

class PluginsApiClient extends CommonApiClient implements PluginsApiClientInterface
{
    const SUB_URL_REGISTER = 'api/plugin/registry/register';
    const SUB_URL_UNREGISTER = 'api/plugin/registry/unregister';
    const SUB_URL_ACK_COMMAND = 'api/plugin/registry/ack/%s';
    const SUB_URL_GET_COMMANDS = 'api/plugin/command/list';

    /** @var PluginRegistryInfoProviderInterface */
    private $registryInfoProvider;

    public function __construct(
        PluginRegistryInfoProviderInterface $registryInfoProvider,
        ClientConfigurationInterface $clientConfiguration,
        OauthTokenList $oauthTokenList = null,
        HttpClientInterface $httpClient = null
    ) {
        parent::__construct($clientConfiguration, $oauthTokenList, $httpClient);

        $this->registryInfoProvider = $registryInfoProvider;
    }

    /**
     * @inheritdoc
     */
    public function getRegistryInfoProvider()
    {
        return $this->registryInfoProvider;
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function registerPlugin()
    {
        $url = sprintf('%s%s', $this->getLiveBaseUrl(), static::SUB_URL_REGISTER);

        return $this->doPublicJsonPostRequest(
            $url,
            $this->buildRegistryRequestEntity(true),
            new PluginRegistryResponseEntity()
        );
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function unregisterPlugin()
    {
        $url = sprintf('%s%s', $this->getLiveBaseUrl(), static::SUB_URL_UNREGISTER);

        return $this->doPublicJsonPostRequest(
            $url,
            $this->buildRegistryRequestEntity(),
            new PluginRegistryResponseEntity()
        );
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function acknowledgePluginCommand($commandId)
    {
        return $this->doPublicJsonPostRequest(
            $this->buildAcknowledgePluginCommandUrl($commandId),
            $this->buildRegistryRequestEntity(),
            new PluginRegistryResponseEntity()
        );
    }

    /**
     * @inheritdoc
     *
     * @pga-return Response<CommandsResponseEntity>
     *
     * @throws \Exception
     */
    public function getCommands($fromTimestamp = null)
    {
        $request = RequestBuilder::get($this->buildGetCommandsUrl($fromTimestamp))
            ->setResponseEntity(new CommandsResponseEntity())
            ->build();

        return $this->getHttpClient()->execute($request);
    }

    /**
     * @param bool $extended whether we should build entity for Register request
     *
     * @return PluginRegistryRequestEntity
     */
    private function buildRegistryRequestEntity($extended = false)
    {
        $requestEntity = new PluginRegistryRequestEntity();
        $requestEntity
            ->setChannel($this->registryInfoProvider->getChannel())
            ->setHost($this->registryInfoProvider->getHost())
        ;

        if ($extended) {
            $requestEntity
                ->setPluginVersion($this->registryInfoProvider->getPluginVersion())
                ->setCmsVersion($this->registryInfoProvider->getCmsVersion())
                ->setSupportedCommands($this->registryInfoProvider->getSupportedCommands())
                ->setCommandEndpoint($this->registryInfoProvider->getCommandEndpoint())
                ->setBusinessIds($this->registryInfoProvider->getBusinessIds())
            ;
        }

        return $requestEntity;
    }

    /**
     * @param string $url
     * @param RequestEntity $requestEntity
     * @param ResponseEntity $responseEntity
     * @return Response
     *
     * @throws \Exception
     */
    private function doPublicJsonPostRequest($url, RequestEntity $requestEntity, ResponseEntity $responseEntity)
    {
        $request = RequestBuilder::post($url)
            ->contentTypeIsJson()
            ->setRequestEntity($requestEntity)
            ->setResponseEntity($responseEntity)
            ->build();

        return $this->getHttpClient()->execute($request);
    }

    /**
     * @param string $commandId
     * @return string
     */
    private function buildAcknowledgePluginCommandUrl($commandId)
    {
        return sprintf('%s%s', $this->getLiveBaseUrl(), sprintf(static::SUB_URL_ACK_COMMAND, $commandId));
    }

    /**
     * @param int|null $fromTimestamp
     * @return string
     */
    private function buildGetCommandsUrl($fromTimestamp = null)
    {
        $url = sprintf('%s%s', $this->getLiveBaseUrl(), static::SUB_URL_GET_COMMANDS);

        if ((int) $fromTimestamp > 0) {
            $url .= sprintf('?from=%s', $fromTimestamp);
        }

        return $url;
    }

    /**
     * @return string
     */
    private function getLiveBaseUrl()
    {
        $url = $this->configuration->getCustomLiveUrl() ?: static::URL_LIVE;

        if (substr($url, -1) != '/') {
            $url .= '/';
        }

        return $url;
    }
}