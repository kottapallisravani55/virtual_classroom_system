const chatContainer = document.querySelector(".chat-container");
const chatMessages = document.querySelector(".chat-messages");
const chatInputForm = document.querySelector(".chat-input-form");
const chatInput = document.querySelector(".chat-input");
const userInfoModal = document.querySelector(".user-info-modal");
const userInfoForm = document.querySelector(".user-info-form");

let chatCode = null;
let senderId = null;

// Handle user information submission
userInfoForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const username = userInfoForm.username.value;
  chatCode = userInfoForm.chatCode.value;

  // Assume user exists in database (Add server-side check for production)
  fetch(`validate_user.php?username=${username}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        senderId = data.user_id;
        userInfoModal.style.display = "none";
        chatContainer.style.display = "block";

        // Fetch chat messages
        fetchMessages();
      } else {
        alert(data.error);
      }
    });
});

// Fetch messages
function fetchMessages() {
  fetch(`fetch_messages.php?chat_code=${chatCode}`)
    .then((response) => response.json())
    .then((messages) => {
      chatMessages.innerHTML = messages
        .map(
          (msg) =>
            `<div><strong>${msg.username}:</strong> ${msg.message} <small>${msg.sent_at}</small></div>`
        )
        .join("");
    });
}

// Send message
chatInputForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const message = chatInput.value;

  fetch("send_message.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ chat_code: chatCode, sender_id: senderId, message }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        chatInput.value = "";
        fetchMessages();
      } else {
        alert("Failed to send message!");
      }
    });
});

// Fetch messages every 5 seconds
setInterval(fetchMessages, 5000);
