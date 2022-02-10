<?php

namespace Inescoin\Entity;

abstract class AbstractEntity
{
	private $isNew = true;

	public function _isNotNew() {
        $this->isNew = false;
        return $this;
    }

    public function _getIsNew() {
        return $this->isNew;
    }

    public function __construct(array $data = []) {
		$this->setDataAsArray($data);
	}

	public function setDataAsArray(array $data) {
		foreach ($data as $key => $value) {
			$setter = 'set' . $this->_convertToSetterKey($key);
			if (method_exists($this, $setter)) {
				$this->{$setter}($value);
			}
		}

		return $this;
	}

	public function getDataAsArray() {
		$output = [];
		$classMethods = get_class_methods($this);

		foreach ($classMethods as $methodName) {
		    if (substr($methodName, 0, 3) === 'get' && $methodName !== 'getDataAsArray') {
		    	$cleanedGetName = str_replace('get', '', $methodName);
		    	$methodNameChanged = $this->_camelCaseToUnderscore($cleanedGetName);
		    	$propertyName = property_exists($this, $methodNameChanged) ? $methodNameChanged : lcfirst($cleanedGetName);
		    	$output[$propertyName] = $this->{$methodName}();
		    }
		}

		return $output;
	}

	private function _convertToSetterKey($key) {
	    return str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
	}


	private function _camelCaseToUnderscore($property) {
	    return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $property)), '_');
	}
}
