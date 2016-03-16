<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2016 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OTPHP;

use Assert\Assertion;

final class HOTP extends OTP implements HOTPInterface
{
    /**
     * HOTP constructor.
     *
     * @param string $label
     * @param string $secret
     * @param int    $counter
     * @param string $digest
     * @param int    $digits
     */
    public function __construct($label, $secret, $counter = 0, $digest = 'sha1', $digits = 6)
    {
        parent::__construct($label, $secret, $digest, $digits);
        $this->setCounter($counter);
    }

    /**
     * @param int $counter
     *
     * @return self
     */
    public function setCounter($counter)
    {
        Assertion::integer($counter, 'Counter must be at least 0.');
        Assertion::greaterOrEqualThan($counter, 0, 'Counter must be at least 0.');

        $this->setParameter('counter', $counter);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCounter()
    {
        return $this->getParameter('counter');
    }

    /**
     * @param int $counter
     *
     * @return $this
     */
    private function updateCounter($counter)
    {
        $this->setCounter($counter);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvisioningUri($google_compatible = true)
    {
        return $this->generateURI('hotp', ['counter' => $this->getCounter()], $google_compatible);
    }

    /**
     * {@inheritdoc}
     */
    public function verify($otp, $counter, $window = null)
    {
        if ($counter < $this->getCounter()) {
            return false;
        }

        if (!is_int($window)) {
            $window = 0;
        }
        $window = abs($window);

        for ($i = $counter; $i <= $counter + $window; ++$i) {
            if ($this->compareOTP($this->at($i), $otp)) {
                $this->updateCounter($i + 1);

                return true;
            }
        }

        return false;
    }
}
