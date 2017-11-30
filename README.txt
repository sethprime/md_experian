SUMMARY - MD Experian
========================

Experian is a Third party service providing a way to validate the users address. this module is providing the validation for Phone number & Email address through
experian service.


Installation:
-------------

Install this module as usual. Please see
http://drupal.org/documentation/install/modules-themes/modules-8

Usage:
------

All the Email & Phone number field is validated through the Experian Service.

For Custom integration, Inject 'md_experian.validation_service' service
in your custom module:

use Drupal\mdvip_experian\Services\MdExperianService;

  public function __construct(MdExperianService $experianValidation) {
    $this->experianValidation = $experianValidation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('md_experian.validation_service')
    );
  }

For Email Address:
$this->experianValidation->validateEmail($emailAddress);

For Phone Number:
$this->experianValidation->validatePhone($phoneNumber);


Dependency
--------
Md Encription
