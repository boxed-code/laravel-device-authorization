<?php

namespace BoxedCode\Laravel\Auth\Device\Fingerprints;

use BoxedCode\Laravel\Auth\Device\Contracts\Fingerprinter;
use Illuminate\Http\Request;

class RequestFingerprinter implements Fingerprinter
{
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function fingerprint(Request $request)
    {
        $data = [];

        foreach ($this->config['keys'] as $name => $section) {
            foreach ($section as $attr) {
                $data[] = $request->$name->get($attr);
            }
        }

        return implode('::', $data);
    }
}