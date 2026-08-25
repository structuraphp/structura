SHELL=bash
SOURCE_DIR = $(shell pwd)
BIN_DIR = ${SOURCE_DIR}/vendor/bin
COMPOSER = composer

_CYAN=\033[36m
_GREEN=\033[32m
_END=\033[0m

define printSection
	@printf "${_CYAN}\n══════════════════════════════════════════════════\n${_END}"
	@printf "${_CYAN} $1 ${_END}"
	@printf "${_CYAN}\n══════════════════════════════════════════════════\n${_END}"
endef

.PHONY: all ## Run all checks
all: fix phpstan rector dependency test

#  _   _      _
# | | | |    | |
# | |_| | ___| |_ __
# |  _  |/ _ \ | '_ \
# | | | |  __/ | |_) |
# \_| |_/\___|_| .__/
#              | |
#              |_|

.PHONY: help
help: ## Displays the list of commands
	$(call printSection,HELP)
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) \
	| sort \
	| awk 'BEGIN {FS = ":.*?## "}; {printf "${_GREEN}%-20s${_END} %s\n", $$1, $$2}' \
	| sed -e 's/##//'

#  _____
# /  __ \
# | /  \/ ___  _ __ ___  _ __   ___  ___  ___ _ __
# | |    / _ \| '_ ` _ \| '_ \ / _ \/ __|/ _ \ '__|
# | \__/\ (_) | | | | | | |_) | (_) \__ \  __/ |
#  \____/\___/|_| |_| |_| .__/ \___/|___/\___|_|
#                       | |
#                       |_|

.PHONY: install
install: clean-vendor install-vendor ## Install the project

.PHONY: clean-vendor
clean-vendor: ## Remove composer dependencies
	$(call printSection,CLEAN VENDOR)
	rm -rf ${SOURCE_DIR}/vendor

.PHONY: install-vendor
install-vendor: ${SOURCE_DIR}/vendor/composer/installed.json ## Install composer dependencies

${SOURCE_DIR}/vendor/composer/installed.json:
	$(call printSection,INSTALL VENDOR)
	$(COMPOSER) --no-interaction install --ansi --no-progress --prefer-dist

#  _____             _ _ _ 
# |  _  |           | (_) |
# | | | |_   _  __ _| |_| |_ _   _
# | | | | | | |/ _` | | | __| | | |
# \ \/' / |_| | (_| | | | |_| |_| |
#  \_/\_\\__,_|\__,_|_|_|\__|\__, |
#                             __/ |
#                            |___/

.PHONY: fix
fix: ## Checks if code style is compliant
	$(call printSection,PHP-CS-FIXER)
	${BIN_DIR}/php-cs-fixer fix -vvv

.PHONY: rector
rector: ## Checks if the quality of the code is compliant
	$(call printSection,RECTOR)
	${BIN_DIR}/rector process --dry-run

.PHONY: rector-process
rector-process: ## Apply if the quality of the code is compliant
	$(call printSection,RECTOR)
	${BIN_DIR}/rector process

.PHONY: phpstan
phpstan: ## Check if the data types are compliant
	$(call printSection,PHPSTAN)
	${BIN_DIR}/phpstan --memory-limit=1G analyse

.PHONY: dependency
dependency: ## Check if the dependency are compliant
	$(call printSection,COMPOSER DEPENDENCY)
	${BIN_DIR}/composer-dependency-analyser

#  _____         _
# |_   _|       | |
#   | | ___  ___| |_
#   | |/ _ \/ __| __|
#   | |  __/\__ \ |_
#   \_/\___||___/\__|

.PHONY: test
test: ## Run unit tests [usage: make test args="--filter=TestName --stop-on-failure"]
	$(call printSection,TEST phpunit)
	${BIN_DIR}/phpunit $(args)

.PHONY: mutation-test
mutation-test: ## Run mutation tests [usage: make test args="--filter=SourceName"]
	$(call printSection,MUTATION TEST)
	${BIN_DIR}/infection --threads=12 $(args)

#  ______                  _
# | ___ \                | |
# | |_/ / ___ _ __   ___| |__
# | ___ \/ _ \ '_ \ / __| '_ \
# | |_/ /  __/ | | | (__| | | |
# \____/ \___|_| |_|\___|_| |_|

# Tolerance: 10% plus 1 microsecond, so that sub-microsecond variants
# (ErrorNoneFormatter, trivial assertions) do not fail on measurement noise.
BENCH_ASSERT = mode(variant.time.avg) < mode(baseline.time.avg) * 1.10 + 1 microsecond

# On a hybrid CPU (Intel P/E cores), pin the run to one performance core,
# otherwise the same code can be measured twice as slow depending on where the
# process lands: make bench BENCH_CPU=2 (see benchmarks/README.md).
BENCH_CPU ?=
BENCH_TASKSET = $(if $(BENCH_CPU),taskset -c $(BENCH_CPU),)

.PHONY: bench
bench: ## Run benchmarks against the stored baseline [usage: make bench args="--filter=AssertBench" BENCH_CPU=2]
	$(call printSection,BENCHMARK)
	$(BENCH_TASKSET) ${BIN_DIR}/phpbench run --report=aggregate --ref=baseline \
		--assert='$(BENCH_ASSERT)' $(args)

.PHONY: bench-baseline
bench-baseline: ## Store the current results as the reference baseline
	$(call printSection,BENCHMARK BASELINE)
	$(BENCH_TASKSET) ${BIN_DIR}/phpbench run --report=aggregate --tag=baseline --progress=none $(args)

.PHONY: bench-report
bench-report: ## Display the stored baseline report
	$(call printSection,BENCHMARK REPORT)
	${BIN_DIR}/phpbench report --ref=baseline --report=aggregate

phar: ## Build PHAR
	$(call printSection,BUILD Phar)
	$(COMPOSER) global require humbug/box
	$(COMPOSER) require --dev bamarni/composer-bin-plugin
	$(COMPOSER) bin box require --dev humbug/box
	$(COMPOSER) install --no-dev -o
	${BIN_DIR}/box compile
