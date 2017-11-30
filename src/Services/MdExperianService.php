<?php

namespace Drupal\md_experian\Services;

use GuzzleHttp\Client;
use Drupal\Component\Serialization\Json;
use Drupal\md_common\Helper\MdTimestampHelper;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Class MdExperianService.
 *
 * @package Drupal\md_lead_forms\Services
 */
class MdExperianService {

  /**
   * GuzzleHttp\Client definition.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;
  /**
   * Drupal\md_experian\Services\MdEncryptionService definition.
   *
   * @var Drupal\md_experian\Services\MdEncryptionService
   */
  protected $encryption;
  /**
   * Drupal\Core\Entity\EntityManagerInterface definition.
   *
   * @var Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs Config Factory & Http Client object.
   *
   * @param \GuzzleHttp\Client $httpClient
   *   HttpClient Service.
   * @param \Drupal\md_experian\Services\MdEncryptionService $encryption
   *   MdEncryptionService Service.
   * @param \Drupal\Core\Entity\EntityManagerInterfacee $entityManager
   *   EntityManagerInterface Service.
   */
  public function __construct(Client $httpClient, MdEncryptionService $encryption, EntityManagerInterface $entityManager) {
    $this->httpClient = $httpClient;
    $this->encryption = $encryption;
    $this->entityManager = $entityManager;
  }

  /**
   * Return a configured Client object.
   */
  public function validateEmail($email) {
    $endpoints = $this->entityManager->getStorage('endpoints')->loadByProperties(['status' => 1]);
    foreach ($endpoints as $val) {
      $endPoint = $val->get('experian_email_endpoint')->value;
      $token = $val->get('experian_email_token')->value;
    }
    $emailMsg = [
      'Verified' => 'Mailbox exists, is reachable, and not known to be illegitimate or disposable.',
      'Unknown' => 'We were unable to conclusively verify or invalidate this address.',
      'Unreachable' => 'Domain does not exist or has no reachable mail exchangers.',
      'Disposable' => 'Domain is administered by a disposable email provider (eg. Mailinator).',
      'Undeliverable' => 'Mailbox does not exist, or mailbox is full, suspended, or disabled.',
      'Illegitimate' => 'Seed, spamtrap, black hole, technical role account or inactive domain.',
    ];

    $data = ["Email" => $email];
    // Decrypt Key.
    $is_valid = $this->encryption->check_encryptProfile();
    if ($is_valid) {
      $token = $this->encryption->encrypt_decrypt('decrypt', $token);
    }
    try {
      $response = $this->httpClient->post($endPoint, [
        'verify' => TRUE,
        'body' => Json::encode($data),
        'headers' => [
          'Auth-Token' => $token,
          'Content-Type' => 'application/json',
        ],
      ]);
      $date = $response->getHeader('Date');
      $data = Json::decode($response->getBody()->getContents(), TRUE);
      $statusMsg = ucfirst($data['Certainty']);

      $emailStatus = [
        'Email_Validation_Message' => $emailMsg[$statusMsg],
        'Email_Validation_Status' => $statusMsg,
        'Email_Validation_Timestamp' => MDTimestampHelper::convertTimestamp($date[0], 'EST', 'GMT'),
      ];

      $data_array = [
        'status_message' => ['status' => $statusMsg, 'message' => 'Please confirm your email.'],
        'response_data' => $emailStatus,
      ];

      return $data_array;
      // Return $response & check the message (Store the message in variable.)
    }
    catch (RequestException $e) {
      $emailStatus = [
        'Email_Validation_Message' => 'Not Validated',
        'Email_Validation_Status' => 'Not Validated',
        'Email_Validation_Timestamp' => MDTimestampHelper::convertTimestamp($date[0], 'EST', 'GMT'),
      ];

      $data_array = [
        'status_message' => ['status' => 'Verified', 'message' => 'Please confirm your email.'],
        'response_data' => $emailStatus,
      ];
      \Drupal::logger('md_experian')->error('Experian error :' . $email . ':Unable to validate new lead on insert.');
      return $data_array;
    }
    catch (\Exception $e) {
      $emailStatus = [
        'Email_Validation_Message' => 'Not Validated',
        'Email_Validation_Status' => 'Not Validated',
        'Email_Validation_Timestamp' => MdTimestampHelper::convertTimestamp($date[0], 'EST', 'GMT'),
      ];
      \Drupal::logger('md_experian')->error('Experian error :' . $email . ':Unable to validate new lead on insert.');
      $data_array = [
        'status_message' => ['status' => 'Verified', 'message' => 'Please confirm your email.'],
        'response_data' => $emailStatus,
      ];
      return $data_array;
    }
  }

  /**
   * Return a configured Client object.
   */
  public function validatePhone($phone) {
    $endpoints = $this->entityManager->getStorage('endpoints')->loadByProperties(['status' => 1]);
    foreach ($endpoints as $val) {
      $endPoint = $val->get('experian_phone_endpoint')->value;
      $token = $val->get('experian_phone_token')->value;
    }
    $phone = preg_replace('/[^0-9]/', '', $phone);
    $phoneMsg = [
      'Verified' => 'Number format validated and number verified',
      'Unknown' => '',
      'Unreachable' => '',
      'Disposable' => '',
      'Undeliverable' => '',
      'Illegitimate' => '',
    ];

    $data = [
      "Number" => $phone,
      "DefaultCountryCode" => "+1",
    ];
    // Decrypt Key.
    $is_valid = $this->encryption->check_encryptProfile();
    if ($is_valid) {
      $token = $this->encryption->encrypt_decrypt('decrypt', $token);
    }
    try {
      $response = $this->httpClient->post($endPoint, [
        'verify' => TRUE,
        'body' => Json::encode($data),
        'headers' => [
          'Auth-Token' => $token,
          'Content-Type' => 'application/json',
        ],
      ]);

      $date = $response->getHeader('Date');
      $data = Json::decode($response->getBody()->getContents(), TRUE);
      $statusMsg = ucfirst($data['Certainty']);

      $phoneStatus = [
        'Phone_Validation_Message' => $data['PhoneType'] . ", " . $phoneMsg[$statusMsg],
        'Phone_Validation_Status' => $statusMsg,
        'Phone_Validation_Timestamp' => MdTimestampHelper::convertTimestamp($date[0], 'EST', 'GMT'),
      ];

      $data_array = [
        'status_message' => [
          'status' => $statusMsg,
          'message_1' => 'Please enter a valid phone number',
          'message_2' => 'Please confirm your phone number.',
        ],
        'response_data' => $phoneStatus,
      ];
      return $data_array;
      // Return $response & check the message.
    }
    catch (\Exception $e) {
      $phoneStatus = [
        'Phone_Validation_Message' => 'Not Validated',
        'Phone_Validation_Status' => 'Not Validated',
        'Phone_Validation_Timestamp' => MdTimestampHelper::convertTimestamp($date[0], 'EST', 'GMT'),
      ];
      // Catches all 4xx and 5xx status codes.
      \Drupal::logger('md_experian')->error('Experian error :' . $phone . ':Unable to validate new lead on insert.');
      $exception_msg = [
        'status_message' => [
          'status' => 'Verified',
          'message_1' => 'Please enter a valid phone number',
        ],
        'response_data' => $phoneStatus,
      ];
      return $exception_msg;
    }
    catch (RequestException $e) {
      watchdog_exception('md_experian', $e->getMessage());
    }
  }

}
