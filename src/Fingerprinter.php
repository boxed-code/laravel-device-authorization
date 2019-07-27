<?php

namespace BoxedCode\Laravel\Auth\Device\Fingerprints;

use BoxedCode\Laravel\Auth\Device\Contracts\Fingerprinter as Contract;
use Illuminate\Http\Request;

class Fingerprinter implements Contract
{
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function fingerprint(Request $request)
    {
        $data = [];

        // Loop through the request data a pluck out the keys 
        // we desire for building the fingerprint.
        foreach ($this->config['keys'] as $name => $section) {
            foreach ($section as $attr) {
                if (is_null($request->$name)) {
                    $data[] = $request->$name()->get($attr);
                    continue;
                }

                $data[] = $request->$name->get($attr);
            }
        }

        return implode('::', $data);
    }
}