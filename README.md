Resource component
==================

### Registry

* resources configurations

### Behaviors

* toggleable : enable/disable, default (eventually per group)
* timestampable
* searchable : elasticsearch (external behavior/out of this component)
* ...

### Auto mapping

With persistence layer, based on behaviors.

### Actions (ADR pattern)

* create
* read
* update
* delete
* toggle
* move
* search
* ...

### Auto Routing

* api
* admin

### Service container builder

* class parameter
* repository
* manager
* event
* form
* table

### Event system

* each action may dispatch a pre/post event 
* onPersist (ex during doctrine onFlush) + recompute helper
