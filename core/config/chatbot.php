<?php


return [
    // Secret token the Node.js chatbot sends in X-Chatbot-Token header
    'api_token' => env('CHATBOT_API_TOKEN', ''),

    // URL of the Node.js chatbot server (internal, never exposed to browser)
    'node_url' => env('CHATBOT_NODE_URL', 'http://localhost:3000'),
];