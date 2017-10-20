Lazyload
============

Javascript which delays the loading of page elements that have the `lazy-module` css tag.

##Options
* `context`: TODO
* `lazy_module_css`: CSS class of lazy modules
* `lazy_css_state`: TODO
* `data_threshold`: Minimum window width for element to render
* `data_always_lazyload`: Set `True` if no minimum threshold
* `data_inc_path`: Path of data to be loaded
* `data_delay_lazyload`: Set `True` if element should be loaded only when in viewport

On page load, finds all items to be lazy loaded, and renders those which should be loaded immediately.
For example those which are already in the viewport and those which are not delayed.
Elements that are yet to be loaded are added to the array toAppear. 

The handler for the window scroll and resize events determines whether these elements should now be loaded ie they are within the viewport or the window is now over the threshold size.
