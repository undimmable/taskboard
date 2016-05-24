<?php

require_once "../bootstrap.php";
require_once "../dal/task.php";

$routes = [
    POST => [
        ROOT => 'api_task_create'
    ],
    GET => [
        ROOT => 'api_task_get_last_n',
        '/\d+/' => 'api_task_get_by_id'
    ],
    PUT => [
        '/\d+/' => 'api_task_update_by_id'
    ],
    DELETE => [
        '/\d+/' => 'api_task_delete_by_id'
    ]
];


function __validate_amount($amount, &$validation_context)
{
    if (is_null($amount)) {
        add_validation_error($validation_context, AMOUNT, 'Price not provided');
        return false;
    }
    $amount = filter_var($amount, FILTER_VALIDATE_INT);
    if (!$amount) {
        add_validation_error($validation_context, AMOUNT, 'Is not a valid number');
        return false;
    }
    if ($amount < get_config_min_amount()) {
        add_validation_error($validation_context, AMOUNT, 'Price cannot be less than ' . get_config_min_amount());
        return false;
    }
    if ($amount > get_config_max_amount()) {
        add_validation_error($validation_context, AMOUNT, 'Price cannot be larger than ' . get_config_max_amount());
        return false;
    }
    return true;
}

function __validate_description($description, &$validation_context)
{
    if (is_null($description) || strlen($description) < 1) {
        add_validation_error($validation_context, DESCRIPTION, 'Description not provided');
        return false;
    }
    return true;
}

function __validate_task_input($amount, $description)
{
    $validation_context = initialize_validation_context();
    __validate_amount($amount, $validation_context);
    __validate_description($description, $validation_context);
    if (validation_context_has_errors($validation_context)) {
        render_bad_request_json(['error' => get_all_validation_errors($validation_context)]);
        die;
    }
}

function api_task_get_by_id($task_id)
{
    $validation_context = initialize_validation_context();
    $task_id = filter_var($task_id, FILTER_VALIDATE_INT);
    $task = dal_task_fetch($task_id);
    if ($task === null) {
        render_not_found();
        die;
    } elseif (!$task) {
        render_bad_request_json(['error' => get_all_validation_errors($validation_context)]);
        die;
    } else {
        render_ok_json($task);
    }
}

function api_task_get_last_n()
{
    if (!is_authorized())
        render_not_authorized_json();
    $user = get_authorized_user();
    $tasks = dal_task_fetch_all_tasks($user[ID], $user[ROLE], parse_integer_param('limit'), parse_integer_param('last_id'));
    if ($tasks === false) {
        render_bad_request_json($tasks);
    } else {
        render_ok_json($tasks);
    }
}

function api_task_update_by_id($id, $task)
{
    echo $task;
    echo $id;
}

function api_task_create()
{
    if (!is_authorized())
        render_not_authorized_json();
    global $user;
    if (is_request_www_form()) {
        $data = $_POST;
    } elseif (is_request_json()) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        render_unsupported_media_type();
        die;
    }
    $customer_id = $user[ID];
    $amount = $data[AMOUNT];
    $description = $data[DESCRIPTION];
    $csrf = $data['csrf_token'];
    if (!is_csrf_token_valid("task", $csrf)) {
        render_not_authorized_json();
        die;
    }
    __validate_task_input($amount, $description);
    $task_id = dal_task_create($customer_id, $amount, $description);
    $task = dal_task_fetch($task_id);
    if (!$task) {
        render_bad_request_json(['error' => get_db_errors()]);
        die;
    } else {
        send_index_event($task[ID], TASK_DESCRIPTION_IDX, $task[DESCRIPTION]);
        $response = [
            'data' => [
                'task' => $task
            ],
            'event' => [
                [
                    'render_data' => 'task'
                ]
            ]
        ];
        render_ok_json($response);
        return true;
    }
}

route_request($routes);