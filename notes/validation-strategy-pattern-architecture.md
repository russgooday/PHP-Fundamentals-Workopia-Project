# Validation Architecture: Strategy Pattern, Code Reuse & Framework Comparisons

This guide covers how to transition from dynamic method dispatching to the **Strategy Pattern** for validation, how to share logic (like `getSize`) across self-contained rule classes, and how enterprise frameworks like **Symfony** and **Laravel** solve these problems.

---

## 1. The Strategy Pattern: From Strings to Rule Objects

In PHP, going from a string like `'same'` with argument `'password'` to an instantiated `SameRule` class is remarkably simple because **PHP allows dynamic class instantiation with `new $className(...$args)`**.

To connect the string to the class, pair the **Strategy Pattern** with a **Rule Factory** or **Rule Map**.

### Step 1: The Strategy Interface & The Rule

Define the contract for all rules:

```php
namespace Framework\Validation;

interface ValidationRule {
    public function validate(mixed $value, ValidationContext $context): bool;
}
```

Now create `SameRule.php`. Notice that the rule argument (`'password'`) is passed into its constructor:

```php
namespace Framework\Validation\Rules;

use Framework\Validation\ValidationRule;
use Framework\Validation\ValidationContext;

class SameRule implements ValidationRule {
    public function __construct(private string $otherField) {}

    public function validate(mixed $value, ValidationContext $context): bool {
        return $value === ($context->data[$this->otherField] ?? null);
    }
}
```

---

### Step 2: The Map / Factory

In `Validator.php`, maintain a map of rule aliases to class names (or use a naming convention like `ucfirst($rule) . 'Rule'`):

```php
class Validator {
    private array $ruleMap = [
        'required' => Rules\RequiredRule::class,
        'email'    => Rules\EmailRule::class,
        'same'     => Rules\SameRule::class,
        'between'  => Rules\BetweenRule::class,
        'min'      => Rules\MinRule::class,
    ];
}
```

---

### Step 3: Instantiating and Running the Rule

When your loop parses `'same:password'`, you have:
* `$ruleName = 'same'`
* `$args = ['password']`

The dispatcher code simply looks up the class, instantiates it dynamically, and validates:

```php
// 1. Look up the class name
$class = $this->ruleMap[$ruleName] ?? null;

if (!$class || !class_exists($class)) {
    throw new \Exception("Validation rule '$ruleName' does not exist.");
}

// 2. Dynamically instantiate the rule, passing the arguments to __construct:
/** @var ValidationRule $rule */
$rule = new $class(...$args); 
// Under the hood, PHP calls: new SameRule('password')

// 3. Execute the strategy:
$context = new ValidationContext($this->data, $type);

if (!$rule->validate($value, $context)) {
    $this->errors[$name] = $this->getMessage($ruleName, ...);
}
```

---

### The Superpower This Unlocks

Your validator can accept **both strings and objects** in the rules array:

```php
$rules = [
    // Standard string syntax
    'email' => ['required', 'email'],

    // Object syntax (no string parsing needed!)
    'password_confirmation' => ['required', new SameRule('password')],

    // Custom one-off project rules:
    'subdomain' => ['required', new NotReservedSubdomainRule()],
];
```

In your loop:
```php
foreach ($rules as $rule) {
    if ($rule instanceof ValidationRule) {
        $ruleInstance = $rule;
    } else {
        [$name, $args] = $this->parseRule($rule);
        $class = $this->ruleMap[$name];
        $ruleInstance = new $class(...$args);
    }

    $ruleInstance->validate($value, $context);
}
```

---

## 2. Sharing Helper Logic (e.g. `getSize`) Across Self-Contained Rules

When rules are separate classes, rules like `MinRule`, `MaxRule`, and `BetweenRule` need shared sizing logic for strings, numbers, arrays, and file uploads.

### Approach 1: An Abstract Base Class (The Classic OOP Way - Recommended)

`MinRule`, `MaxRule`, and `BetweenRule` share an **"is a"** relationship: they are all *size-based rules*.

Create an `abstract class SizeRule` that implements the shared sizing logic:

```php
namespace Framework\Validation\Rules;

use Framework\Validation\ValidationRule;
use Framework\Validation\ValidationContext;

abstract class SizeRule implements ValidationRule {

    /**
     * Common sizing logic for strings, numbers, arrays, and files.
     */
    protected function getSize(mixed $value, ValidationContext $context): int|float {
        if ($context->type === 'numeric') {
            return toFloat($value);
        }

        if (is_array($value)) {
            return count($value);
        }

        // Future-proofing: easily support file uploads here!
        // if ($value instanceof UploadedFile) {
        //     return $value->getSizeInKilobytes();
        // }

        return mb_strlen((string) $value);
    }
}
```

Then concrete rules simply extend `SizeRule`:

```php
class MinRule extends SizeRule {
    public function __construct(private int|float $min) {}

    public function validate(mixed $value, ValidationContext $context): bool {
        return $this->getSize($value, $context) >= $this->min;
    }
}

class BetweenRule extends SizeRule {
    public function __construct(private int|float $min, private int|float $max) {}

    public function validate(mixed $value, ValidationContext $context): bool {
        $size = $this->getSize($value, $context);
        return $size >= $this->min && $size <= $this->max;
    }
}
```

**Why this is great:**
* **Single source of truth:** Adding file upload sizing later is done in **one** place (`SizeRule::getSize`), and `min`, `max`, and `between` immediately support files.
* **Pure OOP:** Clear inheritance hierarchy without polluting unrelated rules.

---

### Approach 2: A Trait for Behavior (The Mixin Way)

If you need a "can do" capability ("Can calculate size") shared across multiple independent classes:

```php
trait CalculatesSize {
    protected function getSize(mixed $value, string $type): int|float {
        if ($type === 'numeric') {
            return toFloat($value);
        }
        if (is_array($value)) {
            return count($value);
        }
        return mb_strlen((string) $value);
    }
}
```

And in rules that need it:
```php
class MinRule implements ValidationRule {
    use CalculatesSize;

    public function __construct(private int|float $min) {}

    public function validate(mixed $value, ValidationContext $context): bool {
        return $this->getSize($value, $context->type) >= $this->min;
    }
}
```

---

### Approach 3: Delegate to `ValidationContext`

Since `ValidationContext` already holds field metadata (like `$type`), it can provide the helper directly:

```php
class ValidationContext {
    public function __construct(
        public array $data,
        public string $type
    ) {}

    public function getSize(mixed $value): int|float {
        // sizing logic here...
    }
}
```

```php
class MinRule implements ValidationRule {
    public function __construct(private int|float $min) {}

    public function validate(mixed $value, ValidationContext $context): bool {
        return $context->getSize($value) >= $this->min;
    }
}
```

---

## 3. How Symfony Tackles This Issue

Symfony implements a specification-driven implementation of the **Strategy Pattern** (modeled after Java’s Bean Validation specification / JSR-380).

Instead of one class with many methods, Symfony breaks validation into three distinct components:

```
[Constraint]          (Data / Configuration: min, max, message)
      +
[ConstraintValidator] (Logic / Execution)
      +
[ExecutionContext]    (State, root data, error collection)
```

---

### 1. How Symfony Shares Comparison Logic: Abstract Base Classes

Just like **Approach 1**, Symfony heavily uses **Abstract Base Classes** in `Symfony\Component\Validator\Constraints`:
* All comparison rules inherit from **`AbstractComparisonValidator`**.
* `EqualToValidator`, `NotEqualToValidator`, `GreaterThanValidator`, and `LessThanValidator` all extend this base class:

```php
abstract class AbstractComparisonValidator extends ConstraintValidator {
    
    // The base class handles comparing numbers, dates, strings, and types:
    public function validate(mixed $value, Constraint $constraint): void {
        $comparedValue = $this->getComparedValue($constraint);

        if (!$this->compareValues($value, $comparedValue)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    // Concrete classes only implement this single comparison method:
    abstract protected function compareValues(mixed $value1, mixed $value2): bool;
}
```

`GreaterThanValidator` only needs to define the comparison:
```php
class GreaterThanValidator extends AbstractComparisonValidator {
    protected function compareValues(mixed $value1, mixed $value2): bool {
        return $value1 > $value2;
    }
}
```

---

### 2. How Symfony Avoids "Type Guessing"

In Laravel and custom engines, a single rule like `'min:5'` tries to guess whether it is validating string length, number value, array count, or file size.

Symfony avoids this ambiguity by providing **explicit, dedicated constraints**:

| What is being measured | Symfony Constraint | Validator Class |
| :--- | :--- | :--- |
| **String length** | `#[Assert\Length(min: 3, max: 20)]` | `LengthValidator` |
| **Array / Collection items** | `#[Assert\Count(min: 1, max: 5)]` | `CountValidator` |
| **Numeric or Date values** | `#[Assert\Range(min: 18, max: 65)]` | `RangeValidator` |
| **File sizes** | `#[Assert\File(maxSize: '2M')]` | `FileValidator` |

---

### 3. How Symfony Solves Cross-Field Validation (`same`)

Symfony solves cross-field comparisons using **`propertyPath`** and an injected **`ExecutionContext`**:

```php
#[Assert\EqualTo(propertyPath: 'password', message: 'Passwords must match')]
public string $passwordConfirmation;
```

Inside `AbstractComparisonValidator`:
```php
protected function getComparedValue(Constraint $constraint) {
    if ($constraint->propertyPath) {
        // Reads directly from the root form/object data:
        $root = $this->context->getRoot();
        return $this->propertyAccessor->getValue($root, $constraint->propertyPath);
    }

    return $constraint->value;
}
```

---

## 4. Architectural Summary: Laravel vs. Symfony

| Feature | **Laravel** | **Symfony** |
| :--- | :--- | :--- |
| **Design Style** | Pragmatic, fast, low ceremony | Strict OOP, specification-driven |
| **Rule Organization** | One class / traits (`ValidatorChecks`) | 2 classes per rule (`Constraint` + `Validator`) |
| **Sizing Logic** | `_getSize()` guesses string vs int vs array | Separate explicit rules (`Length`, `Count`, `Range`, `File`) |
| **Cross-field (`same`)** | Accesses `$this->data` directly | Accesses `$context->getRoot()` via PropertyAccessor |
| **Custom Rules** | Simple closure or single invokable class | Create a `Constraint` DTO and a `ConstraintValidator` |

