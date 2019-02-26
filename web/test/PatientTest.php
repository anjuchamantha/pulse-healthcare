<?php

namespace PulseTest;

use DB;
use PHPUnit\Framework\TestCase;
use Pulse\Exceptions\AccountAlreadyExistsException;
use Pulse\Exceptions\InvalidDataException;
use Pulse\Models\AccountSession\LoginService;
use Pulse\Models\Patient\Patient;
use Pulse\Models\Patient\PatientDetails;

class PatientTest extends TestCase
{
    private static $nic;
    private static $name;
    private static $email;
    private static $phoneNumber;
    private static $address;
    private static $postalCode;
    private static $unusedNic;
    private static $password;

    private static $patientDetails;

    /**
     * @beforeClass
     */
    public static function setSharedVariables()
    {
        \Pulse\Database::init();
        LoginService::setTestEnvironment();
        self::$nic = "978978877V";
        self::$name = "Patient Tester";
        self::$email = "tester@medical.patient";
        self::$phoneNumber = "07655667890";
        self::$address = "Fake Number, Fake Street, Fake City, Fake Province.";
        self::$postalCode = "99999";
        self::$unusedNic = "unused_account_id";
        self::$password = "password";

        self::restoreDetails();

        DB::delete('accounts', "account_id = %s", self::$nic);
        DB::delete('accounts', "account_id = %s", self::$unusedNic);
    }

    /**
     * @afterClass
     */
    public static function deleteSessions()
    {
        LoginService::signOutSession();
    }

    private static function restoreDetails()
    {
        self::$patientDetails = new PatientDetails(
            self::$name,
            self::$nic,
            self::$email,
            self::$phoneNumber,
            self::$address,
            self::$postalCode
        );
    }

    /**
     * @throws AccountAlreadyExistsException
     * @throws InvalidDataException
     * @throws \Pulse\Exceptions\AccountNotExistException
     */
    public function testRequestRegistration()
    {
        Patient::register(self::$patientDetails);
        $query = DB::queryFirstRow("SELECT * FROM patients WHERE account_id=%s", self::$nic);
        $this->assertNotNull($query);
        $this->assertNotNull($query['default_password']);
        $query = DB::queryFirstRow("SELECT * FROM sessions WHERE account_id=%s", self::$nic);
        $this->assertNull($query);
    }

    /**
     * @return mixed
     */
    public static function getPatientDetails(): PatientDetails
    {
        return self::$patientDetails;
    }

    /**
     * @depends testRequestRegistration
     * @throws AccountAlreadyExistsException
     * @throws InvalidDataException
     * @throws \Pulse\Exceptions\AccountNotExistException
     */
    public function testDataInvalidationOfName()
    {
        $this->expectException(InvalidDataException::class);
        self::restoreDetails();
        self::getPatientDetails()->setName("");
        Patient::register(self::$patientDetails);
    }

    /**
     * @depends testRequestRegistration
     * @throws AccountAlreadyExistsException
     * @throws InvalidDataException
     * @throws \Pulse\Exceptions\AccountNotExistException
     */
    public function testDataInvalidationOfEmailEmpty()
    {
        $this->expectException(InvalidDataException::class);
        self::restoreDetails();
        self::getPatientDetails()->setEmail("");
        Patient::register(self::$patientDetails);
    }

    /**
     * @depends testRequestRegistration
     * @throws AccountAlreadyExistsException
     * @throws InvalidDataException
     * @throws \Pulse\Exceptions\AccountNotExistException
     */
    public function testDataInvalidationOfEmailRegex()
    {
        $this->expectException(InvalidDataException::class);
        self::restoreDetails();
        self::getPatientDetails()->setEmail("email.com");
        Patient::register(self::$patientDetails);
    }

    /**
     * @depends testRequestRegistration
     * @throws AccountAlreadyExistsException
     * @throws InvalidDataException
     * @throws \Pulse\Exceptions\AccountNotExistException
     */
    public function testDataInvalidationOfPhoneNumberEmpty()
    {
        $this->expectException(InvalidDataException::class);
        self::restoreDetails();
        self::getPatientDetails()->setPhoneNumber("");
        Patient::register(self::$patientDetails);
    }

    /**
     * @depends testRequestRegistration
     * @throws AccountAlreadyExistsException
     * @throws InvalidDataException
     * @throws \Pulse\Exceptions\AccountNotExistException
     */
    public function testDataInvalidationOfAddressEmpty()
    {
        $this->expectException(InvalidDataException::class);
        self::restoreDetails();
        self::getPatientDetails()->setAddress("");
        Patient::register(self::$patientDetails);
    }

    /**
     * @depends testRequestRegistration
     * @throws AccountAlreadyExistsException
     * @throws InvalidDataException
     * @throws \Pulse\Exceptions\AccountNotExistException
     */
    public function testDataInvalidationOfPostalCodeEmpty()
    {
        $this->expectException(InvalidDataException::class);
        self::restoreDetails();
        self::getPatientDetails()->setPostalCode("");
        Patient::register(self::$patientDetails);
    }
}