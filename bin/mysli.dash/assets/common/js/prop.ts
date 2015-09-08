/// <reference path="common.ts" />

module mysli.js.common
{
    export class Prop
    {
        private _context: any;

        constructor(properties: any, context: any)
        {
            this._context = context;
            this.def(properties);
        }

        /**
         * Get/set any property
         */
        get(property: string): any
        {
            return this[property];
        }
        set(property: string, value: any)
        {
            this[property] = value;
        }

        /**
         * Add default properties.
         * It will not append those values that are already set.
         * @param properties
         */
        def(properties: any): void
        {
            var property: string;

            for (property in properties)
            {
                if (!properties.hasOwnProperty(property))
                    continue;

                if (typeof this[property] === 'undefined')
                    this[property] = properties[property];
                else
                    console.warn('Setting a default property which is already there: '+property);
            }
        }

        /**
         * Push and apply list of properties into the stack!
         * Options had to be predefined with a .def method.
         * If you provide `use` list, those will be set using setter.
         * If any of use values has ! e.g. ['icon!', 'label'], the
         * setter will be used, even if option the same as the
         * one already set (by default for example).
         * @param properties
         * @param use
         */
        push(properties: any, use: string[] = []): void
        {
            var property: string;
            var force: boolean;

            // Check which options to set with setter
            if (use.length)
            {
                for (var i = 0; i < use.length; i++)
                {
                    property = use[i];
                    force = (property.substr(property.length - 1, 1) === '!');
                
                    if (force)
                    {
                        property = property.substr(0, property.length - 1);
                        use[i] = property;
                    }

                    if (typeof this[property] !== 'undefined')
                    {
                        if (typeof properties[property] !== 'undefined')
                        {
                            if (properties[property] !== this[property] || force)
                                this._context[property] = properties[property];
                        }
                        else if (force)
                        {
                            this._context[property] = this[property];
                        }
                    }
                }
            }

            // Run through the rest of the properties
            for (property in properties)
            {
                if (!properties.hasOwnProperty(property))
                    continue;

                if (typeof this[property] !== 'undefined' && use.indexOf(property) === -1)
                {
                    if (this[property] && typeof this[property] === 'object' &&
                        properties[property] && typeof properties[property] === 'object')
                    {
                        this[property] = mix(this[property], properties[property]);
                    }
                    else
                    {
                        this[property] = properties[property];
                    }
                }
            }
        }

        /**
         * Query settings, by path, eg: {icon: {name: 'foo'}} ... path='icon.name' => 'foo'
         * @param path
         * @param def
         */
        q(path: string, def?: any): any
        {
            var last: any = this;
            var segments: string[] = path.split('.');

            for (var i=0; i<segments.length; i++)
            {
                if (typeof last[segments[i]] === 'undefined')
                    return def;
                else
                    last = last[segments[i]];
            }

            return last;
        }
    }
}