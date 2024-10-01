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

I focused on the following DDD principles:

Separation of Concerns: The app is structured into distinct layers, so business logic is kept separate from infrastructure concerns. 
This separation makes the code easier to maintain and understand.

Ubiquitous Language: The design revolves around the business domain. In this case, a Webhook is treated as an entity.

Encapsulation: Business logic is encapsulated within domain entities, which helps prevent unexpected side effects when making changes.

While DDD does add some complexity to the architecture, it really pays off in terms of maintainability and scalability for 
larger applications. For smaller projects, a simpler architecture might have sufficed, but DDD allows for future growth. 
I recognize that the initial implementation could have been quicker with a simpler approach, but sticking to DDD principles means 
that future modifications will be easier to manage and understand.

## Project Structure

Application: Contains the use cases and application logic, including the WebhookProcessor.

Domain: Houses the core business logic and entities, like Webhook.

Infrastructure: Manages external interactions, such as sending webhooks.

Tests: Contains unit and integration tests to ensure everything works as expected.

Command: Includes the logic for running commands in the command line.

## Security Considerations

In a commercial setting I would need to consider the following:

Input Validation: Ensure that all inputs (e.g., webhook URLs) are validated to prevent malicious payloads from being processed.

Error Handling: Proper error handling is implemented to avoid leaking sensitive information in case of failures. 
Logs should be monitored for unusual activity.

Rate Limiting: Implementing rate limiting for webhook endpoints to mitigate potential abuse.

## Thoughts For Future Improvements

- Implementing AWS Simple Queue Service (SQS) for handling webhook requests.

- Integrating logging and monitoring solutions for better observability.

- Enhance retry policies with configurable options to adjust parameters (e.g., max attempts, delays) based on endpoint
characteristics or user preferences.

- A user interface to monitor webhook statuses, including successes and failures.

- Using a database for logging successes/failures, as well as the webhooks/endpoints for scalability.

- Could add more test fixtures for various combinations of success/failures to ensure robustness.