{% for message in messages %}
{{ message }}
{% endfor %}
{% for description in listing.descriptions %}
<div id="description_{{description.description_type}}" class="panel panel-default" data-id="{{description.id}}"
     data-c_id="{{description.c_id}}" data-type="course_description">
    <div class="panel-heading">
        {% if is_allowed_to_edit %}
        <div class="pull-right">
            {% if session_id == description.session_id %}
            <a href="{{ _p.web_self }}?action=delete&amp;id={{description.id}}&amp;{{_p.web_cid_query}}"
               onclick="delete_entry('description_{{description.id}}', this); return false;"
               title="{{'Delete'|get_lang}}">
                <img src="{{ 'delete.png'|icon(22) }}"/>
            </a>

            <a href="{{ _p.web_self }}?action=edit&amp;id={{description.id}}&amp;{{_p.web_cid_query}}"
               title="{{'Edit'|get_lang}}">
                <img src="{{ 'edit.png'|icon(22) }}"/>
            </a>
            {% else %}
            <img title="{{'EditionNotAvailableFromSession'|get_lang}}"
                 alt="{{'EditionNotAvailableFromSession'|get_lang}}"
                 src="{{'edit_na.png'|icon(22)}}" width="22" height="22"
                 style="vertical-align:middle;">
            {% endif %}
        </div>
        {% endif %}
        {{description.title}}
    </div>
    <div class="panel-body">
        {{description.content}}
    </div>
</div>
{% endfor %}
