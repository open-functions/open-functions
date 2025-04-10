<?php

namespace OpenFunctions\Core\Responses\Events;

class AvailableEvent
{
    public string $namespace;
    public string $name;
    public string $identifier;

    /**
     * Constructor to initialize an AvailableEvent object.
     *
     * @param string $namespace  The namespace of the event.
     * @param string $name       The name of the event.
     * @param string $identifier The unique identifier for the event.
     */
    public function __construct(string $namespace, string $name, string $identifier)
    {
        $this->namespace = $namespace;
        $this->name = $name;
        $this->identifier = $identifier;
    }
}