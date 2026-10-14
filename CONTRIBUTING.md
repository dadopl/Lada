# Contributing to Lada

## Workflow

- Work follows [docs/BACKLOG.md](docs/BACKLOG.md). **One backlog step = one branch = one pull request.**
- Branch name: `step/<number>-<short-name>`, e.g. `step/1.2-money`.
- A step is done when CI is green, every new class has its tests, the backlog status is updated and — if the step changes how Lada is used — the usage section of [README.md](README.md) is updated in the same PR.

## Language and tooling

- **PHP 8.5.** Every file starts with `declare(strict_types=1);`.
- Development commands are Composer scripts:

  ```bash
  composer install
  composer test      # PHPUnit
  composer stan      # PHPStan, level max
  composer cs        # PHP-CS-Fixer (dry run); composer cs-fix applies fixes
  composer ci        # everything CI runs
  ```

- How you get PHP 8.5 locally (system package, Docker, …) is up to you; no environment tooling is committed to the repository.

## Architecture: DDD + hexagonal

```
packages/<package>/src/
  Domain/        pure model: aggregates, entities, value objects, enums, domain events, domain exceptions
  Application/   use cases (commands, queries, handlers) and ports (interfaces)
packages/bundle/ adapters: Symfony, Doctrine, Messenger, HTTP controllers
```

- **Dependency rule:** `Domain` depends on nothing; `Application` depends on `Domain`; adapters depend on both. `packages/core` never imports Symfony, Doctrine or a protocol package.
- **Ports** are interfaces in `Application\Port`. The shop implements the shop ports (`CatalogPort`, `CartPort`, …); the bundle implements infrastructure ports (repositories, stores).
- **Protocol packages** (`ucp`, `acp`, `mcp`) are inbound adapters: they map protocol payloads to core commands and core results back to protocol payloads. They contain no purchase logic.
- Value objects are `final readonly`, validate in the constructor (or a named constructor) and are compared with `equals()`.
- Validation regexes anchor the end with `\z`, never `$` (which lets a trailing newline through).
- Domain errors are dedicated exceptions extending the package's base exception, never generic `\RuntimeException`.

## SOLID, DRY, KISS

- One reason to change per class. Small interfaces — a port has only the methods a use case needs.
- Depend on interfaces (ports), inject them through the constructor, no service locators, no static state.
- No duplicated rules: a business rule lives in exactly one place (usually the domain model).
- The simplest thing that works. No abstractions "for later", no inheritance where composition is enough; classes are `final` by default.

## Tests

- **Every class that is not a plain model, enum, DTO, interface or exception has a unit test.** Value objects with behaviour (validation, arithmetic) are tested too.
- Unit tests live next to the package: `packages/<package>/tests/Unit/` mirroring `src/`.
- Every test follows **Arrange – Act – Assert**, with the three sections marked by comments:

  ```php
  #[Test]
  public function it_adds_money_in_the_same_currency(): void
  {
      // Arrange
      $a = Money::of(1000, Currency::of('PLN'));
      $b = Money::of(250, Currency::of('PLN'));

      // Act
      $sum = $a->add($b);

      // Assert
      self::assertTrue($sum->equals(Money::of(1250, Currency::of('PLN'))));
  }
  ```

  When the act is expected to throw, the expectation is set up in *Assert* right before *Act* is executed — keep the comment order `// Arrange`, `// Assert`, `// Act` in that case.
- Test method names describe behaviour in snake case: `it_rejects_a_negative_amount`.
- One behaviour per test; use data providers instead of loops.
- Use test doubles only for ports; never mock value objects.
