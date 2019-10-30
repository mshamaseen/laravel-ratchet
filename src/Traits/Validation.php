<?php
/**
 * Created by PhpStorm.
 * User: shanmaseen
 * Date: 26/03/19
 * Time: 01:39 م
 */

namespace Shamaseen\Laravel\Ratchet\Traits;

use Illuminate\Contracts\Validation\Factory;
use Illuminate\Support\Collection;

trait Validation
{
    /**
     * @param array $required
     */
    function validateRequired($required)
    {
        foreach ($required as $input)
        {
            if(!in_array($input,(array)$this->request))
            {
                $this->error($this->request,$this->conn,$this->requireErrorMessage($input));
            }
        }
    }

    /**
     * @param $input
     * @return string
     */
    function requireErrorMessage($input)
    {
        return $input.' input is required.';
    }

    /**
     * Validate the given request with the given rules.
     *
     * @param  object  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate($request, array $rules,
                             array $messages = [], array $customAttributes = [])
    {
        if($request instanceof Collection)
            $request = $request->toArray();

        return $this->getValidationFactory()->make(
            (array) $request, $rules, $messages, $customAttributes
        )->validate();
    }

    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationFactory()
    {
        return app(Factory::class);
    }
}
