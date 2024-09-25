1. A closure scheduled for parallel execution must not contain
a. variable definitions b.named function definitions
c. "yield" instructions d. files inclusions

2. The following code will :

```php
$config = ['one' => 1, 'two' => 2];
\parallel\run(
    function(&$config) {
        $config['one']++;
        echo $config['one'];
    },
    [$config]
);

echo $config['one'];

sleep(1);

echo $config['one'];

```

a. echo "122" b. echo "211" c. echo "121" d. Produce a runtime error

3. The following code will :

```php
$config = ['one' => 1, 'two' => 2];
\parallel\run(
    function($config) {
        $config['one']++;
        echo $config['one'];
    },
    [$config]
);

echo $config['one'];

sleep(1);

echo $config['one'];

```

a. echo "122" b. echo "211" c. echo "121" d. Produce a runtime error

4. The following code will :
```php
Class Person {
    private $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }
}

$person = new Person('Alice');

\parallel\run(
    function(Person $person) {
        echo $person->getName();
    },
    [$person]
);

echo $person->getName();

```
a. echo Alice b. echo nothing c. echo AliceAlice d. Produce a runtime error


5. The following code will :
```php
// file Person.php 
Class Person {
    private $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }
}
//

include_once("Person.php");

$person = new Person('Alice');

\parallel\run(
    function($data) {
        include_once("Person.php");
        $person = unserialize($data);
        echo $person->getName();
    },
    [serialize($person)]
);

echo $person->getName();

```

a. echo Alice b. echo nothing c. echo AliceAlice d. Produce a runtime error