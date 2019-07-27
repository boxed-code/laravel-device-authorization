<?php

namespace BoxedCode\Laravel\Auth\Device\Fingerprints;

use BoxedCode\Laravel\Auth\Device\Contracts\FingerprintManager as Contract;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Manager;

class FingerprintManager extends Manager implements Contract
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return config()->get('device.default_fingerprinter');
    }

    public function createRequestDriver()
    {
        $config = config()->get('device.fingerprinters.request');

        return new RequestFingerprinter($config);
    }
}