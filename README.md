# Wordpress Heimdall Plugin
> Current Version [1.3.1](https://wordpress.org/plugins/heimdall/)

This plugin is for tracking your client activities.

## Usage
Once the plugin is activated, navigate to **Settings > General** in your WordPress Dashboard. On the **Heimdall** section, there you can find all the customization options.

## Statistics shortcode
<code>[statistics class='' params='' hook='']</code>

  - The **<code>class</code>** attribute is the CSS class of the container element. The result will be like bellow:
    ```html
    <p data-statistics-value="#" class="<specified-class> statistics-$">#</p>
    ```
    **<code>#</code>** is the number of visitors, and **<code>$</code>** is the suffix of the additional CSS class.
    
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
    | statistics-em-5m | More than 5 million |



  - The **<code>params</code>** attribute is for filtering the result. The following parameters are supported:
    - **<code>unique</code>** - To get the number of unique visitors.
    - **<code>visitors</code>** - To get current blog visitors.
    - **<code>network</code>** - To get website visitors.
    - **<code>today</code>** - To get records for current date.

    You can pass multiple parameters by using **<code>","</code>** (comma).
    ```php
    [statistics params='unique,today,visitors']
    ```

  - The **<code>hook</code>** attribute is for filtering the result by a specific action hook.
