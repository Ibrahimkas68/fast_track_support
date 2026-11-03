<?php

test('the home page redirects to the ticket queue', function () {
    $response = $this->get('/');

    $response->assertRedirect('/tickets');
});
