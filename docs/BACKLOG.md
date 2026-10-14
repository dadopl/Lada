# Lada — implementation backlog

The milestones from [PLAN.md](PLAN.md) broken down into small steps. **Every step is one pull request**: small enough to review in one sitting, a passing `composer ci`, its own tests and, where it changes how Lada is used, an update of the usage documentation in [README.md](../README.md).

Rules for every step are in [CONTRIBUTING.md](../CONTRIBUTING.md) (PHP 8.5, DDD + hexagonal architecture, SOLID / DRY / KISS, unit tests in Arrange–Act–Assert).

Legend: ✅ done · 🚧 in progress · ⬜ planned

## Layout

```
packages/
  core/              Lada\Core              framework-free domain + application layer
    src/Domain/        model, value objects, enums, domain exceptions, domain events
    src/Application/   commands, handlers, ports (shop ports + infrastructure ports)
  http-signatures/   Lada\HttpSignatures    RFC 9421 / RFC 9530 / JWK
  ucp/               Lada\Ucp               inbound adapter: Universal Commerce Protocol
  acp/               Lada\Acp               inbound adapter: Agentic Commerce Protocol
  mcp/               Lada\Mcp               inbound adapter: Model Context Protocol
  bundle/            Lada\Bundle            Symfony bundle: DI, routing, Doctrine/Messenger adapters
  testkit/           Lada\Testkit           in-memory fake shop, port contract tests
```

Each package has its own `composer.json` (for a later split with `splitsh-lite`); the root `composer.json` autoloads all of them for development.

## Milestone 1 — Foundation

| # | Step | Package | Status |
| --- | --- | --- | --- |
| 1.1 | Monorepo skeleton: root Composer, PHPUnit, PHPStan (max), PHP-CS-Fixer, Composer scripts | — | ✅ |
| 1.2 | Shared kernel: `Currency`, `Money` (minor units, arithmetic, comparison) | core | ✅ |
| 1.3 | Identifiers: `CheckoutSessionId`, `CartRef`, `OrderRef`, `ProductRef`, `IdempotencyKey`, `Protocol` enum | core | ✅ |
| 1.4 | Cart model: `CartLine`, `Totals`, `Message` + `Severity`, `FulfillmentOption`, `CartSnapshot` (completeness flags) | core | ✅ |
| 1.5 | Catalog model: `Channel`, `PricingContext`, `SearchQuery`, `Product`, `ProductPage` | core | ✅ |
| 1.6 | Buyer, payment and order model: `Buyer`, `Email`, `Address`, `CartDraft` + `DraftLine`, `PaymentInstrument`, `GatewayPayment`, `PlacementResult` (placed / requires action / declined / total changed / out of stock), `OrderSnapshot` | core | ✅ |
| 1.7 | Shop ports: `CatalogPort`, `CartPort`, `OrderPlacementPort`, `OrderPort`, `PaymentHandler` + `PaymentHandlerRegistry` | core | ✅ |
| 1.8 | `CheckoutStatus` enum + `CheckoutStatusResolver` (status derived from `CartSnapshot`) | core | ⬜ |
| 1.9 | `CheckoutSession` aggregate: guarded transitions, domain events | core | ⬜ |
| 1.10 | Infrastructure ports: `CheckoutSessionRepository`, `IdempotencyStore` (atomic claim), `ReplayGuard`, `KeyStore`, `WebhookOutbox` | core | ⬜ |
| 1.11 | `CreateCheckout` command + handler | core | ⬜ |
| 1.12 | `UpdateCheckout` command + handler | core | ⬜ |
| 1.13 | `CancelCheckout` command + handler | core | ⬜ |
| 1.14 | `CompleteCheckout` command + handler (claim → lock → total check → placement → result) | core | ⬜ |
| 1.15 | Queries: `SearchCatalog`, `LookupProducts`, `GetCheckout`, `GetOrder` | core | ⬜ |
| 1.16 | Testkit: in-memory fake shop (catalog, cart, placement, order) + in-memory infrastructure ports | testkit | ⬜ |
| 1.17 | Testkit: abstract port contract tests for shop adapters | testkit | ⬜ |
| 1.18 | Content-Digest (RFC 9530): create and verify `sha-256` / `sha-512` | http-signatures | ⬜ |
| 1.19 | Structured Field Values (RFC 8941) parser/serializer — subset needed by `Signature-Input` / `Signature` | http-signatures | ⬜ |
| 1.20 | Signature base builder (RFC 9421 derived components + headers) | http-signatures | ⬜ |
| 1.21 | JWK import/export + algorithms: ES256, ES384, Ed25519 | http-signatures | ⬜ |
| 1.22 | `MessageSigner` / `MessageVerifier` with `created` / `expires`, clock skew, `ReplayGuard`; RFC test vectors | http-signatures | ⬜ |
| 1.23 | Bundle skeleton: `LadaBundle`, configuration tree (`lada.yaml`), validation of required ports | bundle | ⬜ |
| 1.24 | Bundle: DI wiring of shop ports, payment handlers and core handlers | bundle | ⬜ |

## Milestone 2 — UCP: discovery and catalog

| # | Step | Package | Status |
| --- | --- | --- | --- |
| 2.1 | UCP version + capability model, capability negotiation | ucp | ⬜ |
| 2.2 | UCP profile builder (services, capabilities, payment handlers, keys) | ucp | ⬜ |
| 2.3 | `GET /.well-known/ucp` controller, cache headers | bundle | ⬜ |
| 2.4 | `PlatformProfileFetcher` port + SSRF-safe `HttpClient` adapter with PSR-6 cache | ucp, bundle | ⬜ |
| 2.5 | UCP request authentication: RFC 9421 verification on raw body, agent keys from profile | ucp, bundle | ⬜ |
| 2.6 | UCP catalog mapping + `catalog/search` and `catalog/lookup` endpoints | ucp, bundle | ⬜ |
| 2.7 | UCP error model and error codes | ucp | ⬜ |
| 2.8 | File `KeyStore` + `lada:keys:generate`, `lada:keys:rotate` | bundle | ⬜ |
| 2.9 | `lada:profile:dump`, `lada:waf:paths` | bundle | ⬜ |

## Milestone 3 — UCP: cart and handoff

| # | Step | Package | Status |
| --- | --- | --- | --- |
| 3.1 | DBAL `IdempotencyStore` (claim row, in-flight 409, abandoned claim recovery, TTL) | bundle | ⬜ |
| 3.2 | DBAL `CheckoutSessionRepository` + cleanup of expired sessions | bundle | ⬜ |
| 3.3 | UCP cart mapping + `carts` endpoints | ucp, bundle | ⬜ |
| 3.4 | UCP checkout status mapping (core ↔ UCP) | ucp | ⬜ |
| 3.5 | UCP checkout sessions: create / get / update (full replacement) / cancel | ucp, bundle | ⬜ |
| 3.6 | Cart handoff: `continue_url` generation | core, ucp | ⬜ |

## Milestone 4 — MCP

| # | Step | Package | Status |
| --- | --- | --- | --- |
| 4.1 | MCP server wiring (`mcp/sdk`, `symfony/mcp-bundle`, Streamable HTTP) | mcp, bundle | ⬜ |
| 4.2 | Tools: `search_products`, `get_product` | mcp | ⬜ |
| 4.3 | Tools: `start_checkout`, `set_address`, `choose_shipping`, `review_checkout` | mcp | ⬜ |
| 4.4 | Tools: `place_order` (handoff without token), `get_order` | mcp | ⬜ |
| 4.5 | UCP-over-MCP binding | mcp | ⬜ |
| 4.6 | OAuth 2.1 protected resource metadata (RFC 9728) + token validation | bundle | ⬜ |

## Milestones 5–8

Broken down into steps when milestone 4 is done, following the same rules:

- **5. Sylius bridge + E2E** — Sylius port adapters, Docker E2E environment, first `0.x` release.
- **6. UCP native checkout** — `complete` with payment handlers, outbox webhooks (RFC 9421-signed), `orders`, identity linking.
- **7. ACP** — discovery, `API-Version`, partial-update merge, Bearer auth, SPT payment handler, webhook HMAC, product feed.
- **8. 1.0** — security audit, documentation, versioning policy.
