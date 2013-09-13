<?php
namespace Junior\Clientside;

class Response
{

    public $result;
    public $id;
    public $error_code;
    public $error_message;

    // create a new json rpc response object
    public function __construct($result, $id = null, $error_code = null, $error_message = null)
    {
        $this->result = $this->recursiveUTF8Decode($result);
        $this->id = $id;
        $this->error_code = $error_code;
        $this->error_message = $error_message;
    }

    // return result or error if applicable
    public function __toString()
    {
        if ($this->error_message) {
            return "{$this->error_code}: {$this->error_message}";
        }
    
        return $this->result;
    }

    // recursively decode utf8
    private function recursiveUTF8Decode($result)
    {
        if (is_array($result)) {
            foreach ($result as &$value) {
                $value = $this->recursiveUTF8Decode($value);
            }
            return $result;
        } else {
            return utf8_decode($result);
        }
    }
}