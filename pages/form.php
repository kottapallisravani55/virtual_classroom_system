<form method="post" action="send.php">
    <label for="number">Number:</label>
    <input type="text" name="number" id="number" required>

    <label for="message">Message:</label>
    <textarea name="message" id="message" required></textarea>

    <label for="student_id">Student ID:</label>
    <input type="text" name="student_id" id="student_id" required>

    <fieldset>
        <legend>Provider</legend>
        <label>
            <input type="radio" name="provider" value="infobip" checked> Infobip
        </label>
        <br>
    </fieldset>

    <button type="submit">Send</button>
</form>
