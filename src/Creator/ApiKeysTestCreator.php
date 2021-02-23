<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusMolliePlugin\Creator;

use BitBag\SyliusMolliePlugin\Client\MollieApiClient;
use BitBag\SyliusMolliePlugin\DTO\ApiKeyTest;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ApiKeysTestCreator implements ApiKeysTestCreatorInterface
{
    /** @var MollieApiClient */
    private $mollieApiClient;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        MollieApiClient $mollieApiClient,
        TranslatorInterface $translator
    ) {
        $this->mollieApiClient = $mollieApiClient;
        $this->translator = $translator;
    }

    public function create(string $keyType, string $key = null): ApiKeyTest
    {
        $apiKeyTest = new ApiKeyTest(
            $keyType,
            $key ? true : false,
        );

        if (null === $key || empty(trim($key))) {
            $apiKeyTest->setStatus(self::ERROR_STATUS);
            $apiKeyTest->setMessage($this->translator->trans('bitbag_sylius_mollie_plugin.ui.inser_you_key_first'));

            return $apiKeyTest;
        }

        return $this->testApiKey($apiKeyTest, $key);
    }

    private function testApiKey(ApiKeyTest $apiKeyTest, string $apiKey): ApiKeyTest
    {
        try {
            $client = $this->mollieApiClient->setApiKey($apiKey);

            $methods = $client->methods->allActive(MollieMethodsCreatorInterface::PARAMETERS);
            $apiKeyTest->setMethods($methods);

            return $apiKeyTest;
        } catch (\Exception $exception) {
            $apiKeyTest->setStatus(self::ERROR_STATUS);

            if ($exception->getCode() === 0) {
                $apiKeyTest->setMessage($this->translator->trans('bitbag_sylius_mollie_plugin.ui.api_key_start_with_' . $apiKeyTest->getType()));

                return $apiKeyTest;
            }

            $apiKeyTest->setMessage($this->translator->trans('bitbag_sylius_mollie_plugin.ui.failed_with_test_api_key'));

            return $apiKeyTest;
        }
    }
}
