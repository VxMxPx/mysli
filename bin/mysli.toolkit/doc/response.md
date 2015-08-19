# Response

## List of status, with descriptions:

### 200 OK
Standard response for successful HTTP requests.

### 204 No Content
The server successfully processed the request,
but is not returning any content.

### 301 Moved Permanently
This and all future requests should be directed to the given URI.
Location is required.

### 302 Found
The HTTP response status code 302 Found is a common way of performing
a redirection. The User Agent (e.g. a web browser) is invited by a
response with this code to make a second, otherwise identical,
request, to the new URL specified in the Location field.
The HTTP/1.0 specification (RFC 1945) defines this code,
and gives it the description phrase "Moved Temporarily".
Location is required.

### 303 See Other
The HTTP response status code 303 See Other is the correct way to
redirect web applications to a new URI, particularly after an HTTP
POST has been performed, since RFC 2616 (HTTP 1.1). This response
indicates that the correct response can be found under a different
URI and should be retrieved using a GET method. The specified URI is
not a substitute reference for the original resource.
Location is required.

### 307 Temporary Redirect
In this occasion, the request should be repeated with another URI,
but future requests can still use the original URI.
In contrast to 303, the request method should not be changed
when reissuing the original request. For instance, a POST request
must be repeated using another POST request.
Location is required.

### 400 Bad Request
The request contains bad syntax or cannot be fulfilled.

### 401 Unauthorized
The request requires user authentication.

### 403 Forbidden
The request was a legal request, but the server is refusing to
respond to it. Unlike a 401 Unauthorized response, authenticating
will make no difference.

### 404 Not Found
The requested resource could not be found but may be available again
in the future. Subsequent requests by the client are permissible.

### 410 Gone
Indicates that the resource requested is no longer available
and will not be available again. This should be used when a resource
has been intentionally removed; however, it is not necessary to
return this code and a 404 Not Found can be issued instead.
Upon receiving a 410 status code, the client should not request
the resource again in the future. Clients such as search engines
should remove the resource from their indexes.

### 500 Internal Server Error
A generic error message, given when an unexpected condition was
encountered and no more specific message is suitable.

### 501 Not Implemented
The server either does not recognize the request method, or it lacks
the ability to fulfill the request. Usually this implies
future availability (e.g., a new feature of a web-service API).

### 503 Service Unavailable
The server is currently unavailable (because it is overloaded or down
for maintenance). Generally, this is a temporary state.
