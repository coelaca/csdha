/* signup_invite.js */

export function streamSignupInviteStatus() {
	var signupInviteStream, el;

	el = document.getElementById("signup-invite-items");
	if (!el) return;
	signupInviteStream = new EventSource("/api/stream/signup-invite");
	signupInviteStream.addEventListener("signupInviteStatusChanged", 
		updateSignupInviteStatus);
}

function updateSignupInviteStatus(e) {
	var data, el, status;

	data = JSON.parse(e.data);
	el = document.getElementById("signup-invite-" + data.id + "-status");
	if (!el) return;
	switch (data.emailSent) {
	case null:
		status = 'Sending email';
		break;
	case true:
		status = 'Email sent';
		break;
	case false:
		status = 'Email not sent';
		break;
	}
	el.textContent = status;
}