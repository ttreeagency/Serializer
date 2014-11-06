# Ttree.Serializer - Package for TYPO3 Flow to convert PHP object to and from JSON

This package can be used to convert PHP object to and from JSON. The current version support object tree, but no 
circular references, as there is no way to represent this kind of reference in a JSON file.

## Usage

Use DI to inject the ``Ttree\Serializer\SerializerInterface`` where you need it:

```php
class ObjectUtility {

	/**
	 * @Flow\Inject
	 * @var \Ttree\Serializer\SerializerInterface
	 */
	protected $serializer;

	/**
	 * @param object $object
	 * @return string
	 */
	public function save($object) {
		$json = $this->serializer->serialize($object);
	}
	
	/**
	 * @param string $string
	 * @return object
	 */
	public function load($string) {
		$json = $this->serializer->unserialize($string);
	}

}
```

## Functional Programming

You can also use directly the objects ``Ttree\Serializer\Json\Serialize`` and ``Ttree\Serializer\Json\Serialize`` in a
functionnal style programming:

```php
$serialize = new Serialize();
$json = $serialize(array('Hello', 'World'));
$unserialize = new Unserialize();
$array = $unserialize('["Hello","World"]')
```