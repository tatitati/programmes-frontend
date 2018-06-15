define(function() {

    var events = {
        on: function(eventTarget, eventName, listener) {

            if (!eventTarget._events)
                eventTarget._events = {};

            if (!eventTarget._events[eventName])
                eventTarget._events[eventName] = [];

            eventTarget._events[eventName].push(listener);

            return eventTarget;
        },
        off: function(eventTarget, eventName, listener) {

            // Clear all events
            if (!eventName) {
                eventTarget._events = {};

            } else if (eventTarget._events && eventTarget._events[eventName]) {

                // Clear all listeners for this event
                if (!listener) {
                    eventTarget._events[eventName] = [];

                // Clear individual listener
                } else {
                    var listeners = eventTarget._events[eventName];
                    for (var i = 0; i < listeners.length; i++) {
                        if (listener === listeners[i]) {
                            listeners.splice(i--, 1);
                        }
                    }
                }

            }
            return eventTarget;
        },
        emit: function(eventTarget, eventName, eventArgs) {

            if (eventTarget._events && eventTarget._events[eventName]) {
                var listeners = eventTarget._events[eventName];
                for (var i = 0; i < listeners.length; i++) {
                    listeners[i].call(null, eventArgs);
                }
            }

            return eventTarget;
        }
    };

    return events;
});
