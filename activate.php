<?php
//TODO: when adding a token (from URL, type in, paste) do these things:
// normalize I or L -> 1, O -> 0, but only in the -XXXX-XXXX-XXXX part
// use AJAX to validate it and find the full course name

require_once('../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/enrolltokens/activate.php'));
$PAGE->set_title(get_string('activatecoursetokens', 'local_enrollment_tokens'));
$PAGE->set_heading(get_string('activatecoursetokens', 'local_enrollment_tokens'));
$PAGE->requires->js_call_amd('local_enrollment_tokens/activate', 'init');

$email = '';
// If the visitor is logged in, set the email address
if (isloggedin()) {
    $email = $USER->email;
}

// Start output
echo $OUTPUT->header();
echo '<p class="lead my-5">' . s(get_string('activateintroduction', 'local_enrollment_tokens')) . '</p>';

echo '<form class="lead" method="post" action="do-activate-token.php">';
// input email
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="email" class="col-form-label">' . s(get_string('studentemailaddress', 'local_enrollment_tokens')) . '</label>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="email" class="form-control form-control-lg" id="email" name="email" value="">';
echo '  </div>';
echo '</div>';
// input token[] (the first in an array)
// it shows a minus at the right to remove the token
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="token_001" class="col-form-label">' . s(get_string('tokens', 'local_enrollment_tokens')) . '</label>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <div id="tokens">';
echo '      <div class="input-group mb-3">';
echo '        <input type="text" class="form-control form-control-lg" id="token_001" name="token_code[]">';
echo '        <div class="input-group-append">';
echo '          <button class="btn btn-outline-danger remove-token" type="button">&minus;</button>';
echo '        </div>';
echo '      </div>';
echo '    </div>';
// plus button to add more tokens, an icon
echo '    <button class="btn btn-outline-primary mt-3 add-token" type="button"><i class="fa fa-plus"></i></button>';
echo '  </div>';
echo '</div>';
// enroll button
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right"></div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="submit" class="btn btn-primary btn-lg" value="' . s(get_string('enroll', 'local_enrollment_tokens')) . '">';
echo '  </div>';
echo '</div>';
echo '</form>';
// template for a token
echo '<template id="token-template">';
echo '  <div class="input-group mb-3">';
echo '    <input type="text" class="form-control form-control-lg" name="token_code[]">';
echo '    <div class="input-group-append">';
echo '      <button class="btn btn-outline-danger remove-token" type="button">&minus;</button>';
echo '    </div>';
echo '  </div>';
echo '</template>';
echo $OUTPUT->footer();

// TODO: fix
//
// Due to errors in the moodle-docker setup we have to inject JavaScript this way
//
// Issue: https://github.com/moodlehq/moodle-docker/issues/287 

echo <<<HTML
<script>
// Add button
document.addEventListener('click', function (event) {
  if (!event.target.matches('.add-token')) return;
  const template = document.getElementById('token-template');
  const clone = template.content.cloneNode(true);
  document.getElementById('tokens').appendChild(clone);
});

// Delete button
document.addEventListener('click', function (event) {
  if (!event.target.matches('.remove-token')) return;
  event.target.closest('.input-group').remove();

  // If there are no tokens left, add one
  const tokens = document.getElementById('tokens');
  const token = tokens.querySelector('.input-group');
  if (!token) {
    const template = document.getElementById('token-template');
    const clone = template.content.cloneNode(true);
    tokens.appendChild(clone);
  }
});

//TODO: If student is logged in, add a hint below email field "Enroll yourself at abcd@example.com? <button>Click here.</button>"

// After email address is entered, offer hint for common errors
const emailFixes = {
  "@gamil.com": "@gmail.com",
  "@yahooo.com": "@yahoo.com",
  "@hotmial.com": "@hotmail.com",
  "@outlookk.com": "@outlook.com",
}
document.getElementById('email').addEventListener('input', function (event) {
  const email = event.target.value;
  const domain = email.split('@')[1];
  const fix = emailFixes['@' + domain];
  if (fix) {
    // Show a did you mean button below the input with a clickable fix
    const button = document.createElement('button');
    button.classList.add('btn', 'btn-link');
    button.textContent = 'Did you mean ' + email.replace('@' + domain, fix) + '?';
    button.addEventListener('click', function () {
      event.target.value = email.replace('@' + domain, fix);
      button.remove();
    });
    event.target.insertAdjacentElement('afterend', button);
  }
});

// When page loads, apply ?email=XXX&token_code[]=YYY&token_code[]=ZZZ from URL
const url = new URL(window.location.href);
const email = url.searchParams.get('email');
if (email) {
  document.getElementById('email').value = email;
}
const tokenCodes = url.searchParams.getAll('token_code[]');
if (tokenCodes.length) {
  const tokens = document.getElementById('tokens');
  // Remove first token if it's there
  const firstToken = tokens.querySelector('.input-group');
  if (firstToken) {
    firstToken.remove();
  }
  tokenCodes.forEach(function (tokenCode) {
    const template = document.getElementById('token-template');
    const clone = template.content.cloneNode(true);
    clone.querySelector('input').value = tokenCode;
    tokens.appendChild(clone);
  });
}






</script>
HTML;


