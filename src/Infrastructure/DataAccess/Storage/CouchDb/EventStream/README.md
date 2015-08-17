# Events

## Get all events for an AggregateRoot from CouchDB

```
http://hb-showcase-cms.local:5984/honeybee_dev/_design/default_views/_view/user_event_stream?startkey=[%22user-01d7ec03-6551-4b4d-846a-166280ce715e-de_DE-1%22,%201]&endkey=[%22user-01d7ec03-6551-4b4d-846a-166280ce715e-de_DE-1%22,%20{}]&reduce=false&include_docs=true&limit=100
```
