# MTECH Coursedog

The MTECH Coursedog plugin provides an interface with the Coursedog Curriculum API.

## Shortcodes

### Types

- cost
- program_length
- program_length_mobile
- certs
- registration_range
- registration_table
- prereqs
- materials_required
- materials_optional

### Example usage

In the following example, "electrical" is the program slug set in the dashboard and "cost" is the shortcode type:

[mtech-coursedog program_slug="electrical" type="cost"]

## Settings page

The MTECH Coursedog settings page allows for configurations in the following tabs:

### API tab

- Setting API credentials (a Coursedog username and password for an account with API access).

### Shortcodes tab

- Adding, editing and removing programs
- Adding, editing and removing shortcodes
- Deleting shortcodes transients

#### Program slug

This value corresponds to the program_slug value in the shortcode instances.

#### Coursedog Program ID
When a "Coursedog Program ID" value is set for a program, the Coursedog API is queried for that exact program.
This value can be found by querying the Coursedog Curriculum API for all programs.
