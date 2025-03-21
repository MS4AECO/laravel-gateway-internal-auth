# Security Policy

## Important Notice

This package is publicly available as part of our learning journey in security development. We are currently developing our security practices and do not have dedicated experienced security developers on our team. While we take security seriously, we do not make formal commitments regarding response times or comprehensive vulnerability management at this stage of development.

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 0.1.x   | :white_check_mark: |

## Reporting a Vulnerability

We appreciate your efforts to responsibly disclose your findings. As a learning project, we especially welcome security feedback that can help us improve.

### How to Report a Vulnerability

**DO NOT** file a public issue on the GitHub repository for security vulnerabilities.

Instead, please email us at ms4aeco@bimms.com with the following information:

1. Description of the vulnerability
2. Steps to reproduce the issue
3. Potential impact of the vulnerability
4. Any potential solutions you might suggest

### What to Expect

- **Initial Response**: We will acknowledge receipt of your vulnerability report as soon as possible, though response times may vary based on our current capacity.
- **Status Updates**: We will aim to provide updates about our progress as we work to address the vulnerability.
- **Resolution Timeline**: While we strive to address security vulnerabilities in a timely manner, as a learning project without dedicated security staff, we cannot guarantee specific resolution timeframes.

### Security Best Practices When Using This Package

1. Always use HTTPS for API communications
2. Store authentication keys and secrets using environment variables
3. Implement proper access controls for your API endpoints
4. Regularly rotate your API keys and Gateway secrets
5. Monitor logs for unauthorized access attempts
6. Keep this package and its dependencies up to date
7. Follow the principle of least privilege when configuring access permissions

## Security Updates

Security updates will be released as patch versions and announced in:

1. The [CHANGELOG.md](./CHANGELOG.md)
2. Release notes on GitHub

We encourage all users to sign up for GitHub notifications on this repository to receive security update announcements.

## Disclaimer

This project is in active development, and our security practices are evolving. We do not make any guarantees regarding our security response capabilities or timelines. By using this package, you acknowledge that you understand its current limitations. We value community feedback as it helps us improve our security posture and learn best practices.
