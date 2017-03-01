* Now you can add the field to your content class and use it:
    * in twig template, you can use the `ez_render_field` function:
    ```php
    {{ ez_render_field(
        content,
        'remote_image',
        {
            'parameters':
            {
                'format': 'Medium'
            }
        }
    ) }}

    ```
