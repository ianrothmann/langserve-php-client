# LangServe PHP Client

## Purpose
This package provides a PHP client for the LangServe API, allowing you to easily call API endpoints such as `invoke`, `batch`, and `stream`. It simplifies the process of sending requests and handling responses from your LangServe project.

It was designed to work with vanilla PHP so it can be included with any framework (such as Laravel).

https://www.langchain.com/langserve

## Installation
To install the package, use Composer:

```bash
composer require ianrothmann/langserve-php-client
```

## Dependencies
This package requires:
- PHP 8.0 or higher
- Symfony HttpClient component, to support communication and streaming capabilities.

Ensure these are included in your project by adding them to your composer.json if not already present:

```bash
composer require symfony/http-client
```

## Usage
First, instantiate the `RemoteRunnable` with the base URL of your LangServe API:

```php
use IanRothmann\LangServePhpClient\RemoteRunnable;

$runnable = new RemoteRunnable('http://localhost:8100/summarize/');
```

You can optionally add a Bearer token as the second parameter (remember to implement it in LangServe):
```php
$runnable = new RemoteRunnable('http://localhost:8100/summarize/');
```

You have some different options for adding headers and authentication:
```php
$runnable->authenticateWithBearerToken('your-token'); //Add a Bearer token header with the token
$runnable->authenticateWithXToken('your-token'); //Add the X-Token header, if you implemented it according to the LangServe Examples with X-Token
$runnable->addHeader('Key', 'Value'); //Add any other header
```

### Invoke
Invoke a single API call:

```php
$input = ['text' => 'Hello, this is something for you to summarize'];
$response = $runnable->invoke($inputs);
dd($response->getRunId(), $response->getContent(), $response->getTokenUsage(), $response->getData(), $response->toJson());
```

### Batch
Invoke multiple API calls in a batch:

```php
$input = [['text' => 'Hello, this is something for you to summarize'],['text' => 'Hello, this is more text for you to summarize']];
$batchResponse = $runnable->batch($inputs);

foreach ($batchResponse->getResponses() as $response) {
    //$response->getRunId(),
    //$response->getContent(),
    //$response->getTokenUsage());
}
```

### Stream
Handle streaming responses:

```php
$result = $runnable->stream($input, function($response) {
    //Do something with the content here
    $response->getContent();
});
// Finally, this gives you the full content after everything has been streamed:
$result->getContent();
```

### Response Classes
Responses from `invoke` and `batch` are handled through `RemoteRunnableResponse` and `RemoteRunnableBatchResponse`, respectively. `stream` initially provides `RemoteRunnableStreamEvent` for each received event, and finally, a `RemoteRunnableStreamResponse` aggregates all events to compile the total content.

## Exceptions
This client can throw several exceptions based on API response:
- `InternalServerErrorException` for server errors.
- `MalformedInputException` for input schema mismatches.
- `NotFoundException` when the requested resource is not found.
- `RemoteInvocationException` for errors during the remote invocation.

These exceptions help in accurately diagnosing issues with API interactions.

## Contributions
Any contributions to this package is welcome. I'm looking to add more functionality to it and will work on it as required. 