# Wordpress Heimdall Plugin
> Current Version [1.3.0](https://github.com/Rmanaf/wp-heimdall)

A simple way to tracking clients activities.

## Usage
By default, the Plugin tracks the **<code>wp_footer</code>** action hook. In the General settings page You can change the action Hooks.

The Plugin also adds a new shortcode.

<code>[statistics class='' params='' hook='']</code>

  - The **<code>class</code>** attribute is the class of container element. The Shortcode result is:
    ```html
    <p data-statistics-value="#" class="<specified-class> statistics-$">#</p>
    ```
    **<code>#</code>** is the number of visitors and **<code>$</code>** is a suffix for an additional class to control the element style according to the Value. You can see the defined extra classes in the following list:
    
    | Class | Conditions |
    |---| ---|
    | statistics-lt-10 | Between 0 and 10 |
    | statistics-lt-50 | ~ 10 - 50 |
    | statistics-lt-100 | ~ 50 - 100 |
    | statistics-lt-500 | ~ 100 - 500 |
    | statistics-gt-500 | ~ 500 - 1k |
    | statistics-gt-1k | ~ 1k - 5k |
    | statistics-gt-5k | ~ 5k - 10k |
    | statistics-em-10k | ~ 10k - 1m |
    | statistics-em-1m | ~ 1m - 5m |
    | statistics-em-5m | Even more than 5 million |



  - The **<code>params</code>** attribute uses for filtering. The following parameters are supported:
    - **<code>unique</code>** - To get the number of unique visitors.
    - **<code>visitors</code>** - To get current blog visitors.
    - **<code>network</code>** - To get website visitors.
    - **<code>today</code>** - To get records for current date.

    You can pass multiple parameters by using **<code>","</code>** (comma) between them.
    ```php
    [statistics params='unique,today,visitors']
    ```

  - The **<code>hook</code>** attribute is for filtering by specific action hook.


## Bug & Issues Reporting
If you faced any issues, please tell us on [Github](https://github.com/Rmanaf/wp-heimdall/issues/new)
