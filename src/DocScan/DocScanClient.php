<?php

declare(strict_types=1);

namespace Yoti\DocScan;

use Yoti\Constants;
use Yoti\DocScan\Session\Create\CreateSessionResult;
use Yoti\DocScan\Session\Create\FaceCapture\CreateFaceCaptureResourcePayload;
use Yoti\DocScan\Session\Create\FaceCapture\UploadFaceCaptureImagePayload;
use Yoti\DocScan\Session\Create\SessionSpecification;
use Yoti\DocScan\Session\Instructions\Instructions;
use Yoti\DocScan\Session\Retrieve\Configuration\SessionConfigurationResponse;
use Yoti\DocScan\Session\Retrieve\GetSessionResult;
use Yoti\DocScan\Session\Retrieve\Instructions\ContactProfileResponse;
use Yoti\DocScan\Session\Retrieve\Instructions\InstructionsResponse;
use Yoti\DocScan\Support\SupportedDocumentsResponse;
use Yoti\Media\Media;
use Yoti\Util\Config;
use Yoti\Util\Env;
use Yoti\Util\PemFile;
use Yoti\Util\Validation;

/**
 * Client used for communication with the Yoti Doc Scan service,
 * where any signed request is required.
 * <p>
 * The {@code DocScanClient} is to be used by clients to facilitate
 * requests to the Yoti Doc Scan system where any signed requests are
 * required.  Using the supplied models, clients can build requests
 * and perform the requests to the Doc Scan system.
 */
class DocScanClient
{
    /**
     * @var Service
     */
    private $docScanService;

    /**
     * DocScanClient constructor.
     *
     * @param string $sdkId
     *   The SDK identifier generated by Yoti Hub when you create your app.
     * @param string $pem
     *   PEM file path or string
     * @param array<string, mixed> $options (optional)
     *   SDK configuration options - {@see \Yoti\Util\Config} for available options.
     *
     * @throws \Yoti\Exception\PemFileException
     */
    public function __construct(
        string $sdkId,
        string $pem,
        array $options = []
    ) {
        Validation::notEmptyString($sdkId, 'SDK ID');
        $pemFile = PemFile::resolveFromString($pem);

        // Set API URL from environment variable.
        $options[Config::API_URL] = $options[Config::API_URL] ?? Env::get(Constants::ENV_DOC_SCAN_API_URL);

        $config = new Config($options);

        $this->docScanService = new Service($sdkId, $pemFile, $config);
    }

    /**
     * Creates a session within the Yoti Doc Scan session
     * using the supplied specification.
     *
     * @param SessionSpecification $sessionSpecification
     * @return CreateSessionResult
     * @throws Exception\DocScanException
     */
    public function createSession(SessionSpecification $sessionSpecification): CreateSessionResult
    {
        return $this->docScanService->createSession($sessionSpecification);
    }

    /**
     * Retrieves the state of a previously created Yoti Doc Scan session.
     *
     * @param string $sessionId
     * @return GetSessionResult
     * @throws Exception\DocScanException
     */
    public function getSession(string $sessionId): GetSessionResult
    {
        return $this->docScanService->retrieveSession($sessionId);
    }

    /**
     * Deletes a previously created Yoti Doc Scan session and all
     * of its related resources.
     *
     * @param string $sessionId
     * @throws Exception\DocScanException
     */
    public function deleteSession(string $sessionId): void
    {
        $this->docScanService->deleteSession($sessionId);
    }


    /**
     * Retrieves media related to a Yoti Doc Scan session based
     * on the supplied media ID.
     *
     * @param string $sessionId
     * @param string $mediaId
     * @return Media
     * @throws Exception\DocScanException
     */
    public function getMediaContent(string $sessionId, string $mediaId): Media
    {
        return $this->docScanService->getMediaContent($sessionId, $mediaId);
    }

    /**
     * Deletes media related to a Yoti Doc Scan session based
     * on the supplied media ID.
     *
     * @param string $sessionId
     * @param string $mediaId
     * @throws Exception\DocScanException
     */
    public function deleteMediaContent(string $sessionId, string $mediaId): void
    {
        $this->docScanService->deleteMediaContent($sessionId, $mediaId);
    }

    /**
     * Gets a list of supported documents.
     *
     * @param bool $isStrictlyLatin
     * @return SupportedDocumentsResponse
     * @throws Exception\DocScanException
     */
    public function getSupportedDocuments(bool $isStrictlyLatin = false): SupportedDocumentsResponse
    {
        return $this->docScanService->getSupportedDocuments($isStrictlyLatin);
    }

    /**
     * Creates a Face Capture resource, that will be linked using
     * the supplied requirement ID
     *
     * @param string $sessionId
     * @param CreateFaceCaptureResourcePayload $createFaceCaptureResourcePayload
     * @return Session\Retrieve\CreateFaceCaptureResourceResponse
     * @throws Exception\DocScanException
     */
    public function createFaceCaptureResource(
        string $sessionId,
        CreateFaceCaptureResourcePayload $createFaceCaptureResourcePayload
    ): Session\Retrieve\CreateFaceCaptureResourceResponse {
        return $this->docScanService->createFaceCaptureResource($sessionId, $createFaceCaptureResourcePayload);
    }

    /**
     * Uploads an image to the specified Face Capture resource
     *
     * @param string $sessionId
     * @param string $resourceId
     * @param UploadFaceCaptureImagePayload $uploadFaceCaptureImagePayload
     * @throws Exception\DocScanException
     */
    public function uploadFaceCaptureImage(
        string $sessionId,
        string $resourceId,
        UploadFaceCaptureImagePayload $uploadFaceCaptureImagePayload
    ): void {
        $this->docScanService->uploadFaceCaptureImage($sessionId, $resourceId, $uploadFaceCaptureImagePayload);
    }

    /**
     * Fetches the configuration for the given sessionID.
     *
     * @param string $sessionId
     * @return SessionConfigurationResponse
     * @throws Exception\DocScanException
     */
    public function getSessionConfiguration(string $sessionId): SessionConfigurationResponse
    {
        return $this->docScanService->fetchSessionConfiguration($sessionId);
    }

    /**
     * Sets the IBV instructions for the given session
     *
     * @throws Exception\DocScanException
     */
    public function putIbvInstructions(string $sessionId, Instructions $instructions): void
    {
        $this->docScanService->putIbvInstructions($sessionId, $instructions);
    }

    /**
     * Fetches any currently set instructions for an IBV session.
     *
     * @param string $sessionId
     * @return InstructionsResponse
     * @throws Exception\DocScanException
     */
    public function getIbvInstructions(string $sessionId): InstructionsResponse
    {
        return $this->docScanService->getIbvInstructions($sessionId);
    }

    /**
     * Fetches the instructions PDF associated with an In-Branch Verification session.
     *
     * @param string $sessionId
     * @return Media
     * @throws Exception\DocScanException
     */
    public function getIbvInstructionsPdf(string $sessionId): Media
    {
        return $this->docScanService->getIbvInstructionsPdf($sessionId);
    }

    /**
     * Fetches the associated instructions contact profile for the given In-Branch Verification session
     *
     * @param string $sessionId
     * @return ContactProfileResponse
     * @throws Exception\DocScanException
     */
    public function fetchInstructionsContactProfile(string $sessionId): ContactProfileResponse
    {
        return $this->docScanService->fetchInstructionsContactProfile($sessionId);
    }

    /**
     * Triggers an email notification for the IBV instructions at-home flow.
     * This will be one of:
     *  - an email sent directly to the end user, using the email provided in the ContactProfile
     *  - if requested, a backend notification using the configured notification endpoint
     *
     * @throws Exception\DocScanException
     */
    public function triggerIbvEmailNotification(string $sessionId): void
    {
        $this->docScanService->triggerIbvEmailNotification($sessionId);
    }
}
