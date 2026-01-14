## Project Overview

This is a tool for setting up project structure and configuration for PHP packages in
the [PHPTG](https://github.com/phptg). It is based on [vjik/scaffolder](https://github.com/vjik/scaffolder) and
automatically creates and configures essential project files including `composer.json`, GitHub Actions workflows,
configuration files, and documentation structure.

The scaffolder uses a decision-based architecture where `Change` objects decide what modifications to apply,
and `Fact` objects provide contextual information. This project extends the base library with PHPTG-specific
Changes and Facts for creating standardized project structures.

## Commands

### Code Quality

```bash
# Run PHPStan static analysis
composer phpstan

# Fix code style (PER-CS3.0 standard)
composer cs-fix

# Run Rector automated refactoring
composer rector

# Run Composer Dependency Analyser
composer dependency-analyser
```

### Testing

There is currently no test suite configured in this project.

## Architecture

This project extends [vjik/scaffolder](https://github.com/vjik/scaffolder) with PHPTG-specific customizations.

### Entry Point

**src/run.php** - The main entry point that:
- Loads changes from `src/changes.php`
- Loads facts from `src/facts.php`
- Loads params from `src/params.php`
- Creates and runs the `Vjik\Scaffolder\Runner`

The Runner orchestrates the scaffolding process by executing Changes in sequence, resolving Facts on-demand,
and providing a Context for file operations.

### Project Structure

**src/Change/** - Contains PHPTG-specific Change implementations for generating project files and configurations

**src/Fact/** - Contains custom Facts for gathering user preferences and project information

**src/changes.php** - Defines the complete scaffolding sequence and composer.json customizations for PHPTG

**src/facts.php** - Registers all custom Facts

**src/params.php** - Provides default values for PHPTG projects

## Key Patterns

**Decision-Based Changes**: Changes inspect Context and decide whether to apply. This allows idempotent
operations - re-running applies only what's needed.

**Fact Resolution**: Facts are resolved on-demand and cached. This allows interactive prompts only when
actually needed, and facts can depend on other facts or file content.

**Applier Callables**: Changes return callables rather than executing directly. This separates decision
logic from execution, allowing the Command to collect all planned changes before applying.

**Conditional Changes**: Uses `ChangeIf` wrapper to apply changes only when a Fact resolves to true.

## Development Guidelines

### Code Style

- **Do NOT add comments inside methods** unless explicitly requested. Code should be self-explanatory.
- **Do NOT use `assert()`** for type assertions or runtime checks. Rely on PHPStan's static analysis instead.
- **PHPStan ignore for `ComposerJson`**: When using `Context::getFact(ComposerJson::class)`, add `// @phpstan-ignore argument.type` to suppress the template covariance error.

### Fact Normalization

When implementing a `Fact` that accepts a value from a command-line option and prompts the user when the option
is not provided, both paths must use the same normalizer to ensure consistent validation:

- Extract the normalizer into a private static method (e.g., `normalize()`)
- Use the normalizer for option values: `return self::normalize($value);`
- Use first-class callable syntax for interactive prompts: `normalizer: self::normalize(...)`

### File Writing

- **Use `writeTextFile()`** for text files (LICENSE, README.md, composer.json, .php files, etc.)
- **Use `writeFile()`** only for binary files or when exact control over file content is required
