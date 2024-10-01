# Webhook Send Service

## Overview
The Webhook Processor is a PHP Symfony application designed to manage and send webhook notifications efficiently,
utilizing an exponential backoff strategy for retries on failure. The application follows Domain-Driven Design (DDD)
principles to structure the codebase for maintainability, scalability, and clarity.

## Installation and Commands

To set up the project, ensure you have PHP (version 7.4 or later) and Composer installed on your machine.

1. **Clone the repository:**
   ```bash
   git clone git@github.com:lucyferrabee/webhook-send-service.git
   cd webhook-send-service
   composer install
2. **Run the process webhooks command**
   ```bash
   cd webhook-service
   php bin/console app:process-webhooks
3. **Run tests**
   ```bash
   cd webhook-service
   ./vendor/bin/phpunit

## Design Choices

The DDD principles focus on:

Separation of Concerns: The application is divided into distinct layers, ensuring that business logic is separate
from infrastructure concerns. This improves maintainability and allows for clearer understanding and management of the codebase.

Ubiquitous Language: The code is designed around the business domain, using a Webhook in this instance as an entity. 

Encapsulation: Business logic is encapsulated within domain entities, reducing the risk of unexpected side effects when changes are made.

While DDD introduces some complexity in terms of architecture, it pays off in maintainability and scalability for larger applications.
For smaller projects, a simpler architecture might have been sufficient, but DDD prepares the application for future growth.
Initial implementations might have been faster if a simpler approach was taken, but adhering to DDD principles ensures that
future modifications are easier to manage and understand.

## Project Structure

Application: Contains use cases and application logic, including the WebhookProcessor.

Domain: Holds the core business logic and entities, such as Webhook.

Infrastructure: Manages external interactions like sending webhooks.

Tests: Contains unit and integration tests to validate the functionality.

Command: Contains the logic for running the command on the command line.

## Security Considerations

Input Validation: Ensure that all inputs (e.g., webhook URLs) are validated to prevent malicious payloads from being processed.

Error Handling: Proper error handling is implemented to avoid leaking sensitive information in case of failures. Logs should be monitored for unusual activity.

Rate Limiting: Implementing rate limiting for webhook endpoints to mitigate potential abuse.

## Thoughts For Future Improvements

- Implementing AWS Simple Queue Service (SQS) for handling webhook requests could enhance reliability and allow for
asynchronous processing, improving performance and scalability.

- Integrating logging and monitoring solutions (e.g., ELK stack or Prometheus) would provide better insights into webhook 
processing, allowing for proactive issue resolution.

- Enhance retry policies with configurable options to adjust parameters (e.g., max attempts, delays) based on endpoint
characteristics or user preferences.

- A user interface to monitor webhook statuses, including successes and failures, could provide valuable insights for end-users.

- Adding support for different protocols (e.g., WebSocket) could extend the application’s functionality to cater to various use cases.