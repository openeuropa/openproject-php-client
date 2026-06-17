#!/usr/bin/env bash
#
# Generates an OpenProject API token for the seeded admin user on the
# throwaway instance and prints only the token's plain value.
#
# The throwaway instance must be up (it is behind a compose profile, so it
# never starts with normal tooling):
#
#   docker compose --profile openproject up -d   # first boot seeds, ~minutes
#
# Then, to run the integration suite from inside the compose network:
#
#   TOKEN=$(scripts/generate-api-token.sh)
#   docker compose --profile openproject run --rm \
#     -e OPENPROJECT_BASE_URL=http://openproject \
#     -e OPENPROJECT_API_TOKEN="$TOKEN" \
#     -e OPENPROJECT_HOST_HEADER=localhost:8080 \
#     php composer test:integration
#
# rails runner writes an informational log line to stdout before the token,
# so the output is filtered down to the "opapi-" token line.
set -euo pipefail

docker compose --profile openproject exec -T openproject bash -lc \
  "cd /app && bundle exec rails runner \"puts Token::API.create!(user: User.find_by!(login: 'admin')).plain_value\"" \
  | grep -E '^opapi-' | tr -d '\r'
