<!-- Message Modal (Ask Question) -->
<div id="messageModal" class="modal">
    <div class="modal-content slide-up">
        <span onclick="closeMessageModal()" class="close">&times;</span>
        <h3 style="margin-bottom: 20px; color: #1a73e8;">Send Message</h3>
        
        <form id="messageForm" onsubmit="sendMessage(event)">
            <input type="hidden" id="receiverId" name="receiver_id">
            <input type="hidden" id="reviewId" name="review_id">
            <input type="hidden" id="reviewType" name="review_type">
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Your Message:</label>
                <textarea id="messageText" name="message" rows="4" required 
                          style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                          placeholder="Type your question here..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn" style="background: #1a73e8; flex: 1;">Send Message</button>
                <button type="button" onclick="closeMessageModal()" class="btn" style="background: #6c757d; flex: 1;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openMessageModal(reviewId, reviewType, receiverId) {
    document.getElementById('receiverId').value = receiverId;
    document.getElementById('reviewId').value = reviewId;
    document.getElementById('reviewType').value = reviewType;
    document.getElementById('messageModal').style.display = 'block';
}

function closeMessageModal() {
    document.getElementById('messageModal').style.display = 'none';
    document.getElementById('messageForm').reset();
}

function sendMessage(event) {
    event.preventDefault();
    
    const formData = new FormData(document.getElementById('messageForm'));
    
    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Message sent successfully!');
            closeMessageModal();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error sending message. Please try again.');
    });
}

window.onclick = function(event) {
    const modal = document.getElementById('messageModal');
    if (event.target == modal) {
        closeMessageModal();
    }
}
</script>