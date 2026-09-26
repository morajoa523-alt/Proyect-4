<?php require_once APP . '/views/inc/headerIniChat.php' ?>
<?php require_once APP . '/views/inc/Sidebar.php' ?>





<div class="conten-base">
    <div class="messaging-app">
        <div class="chat-list">
            <div class="chat-list-header">
                <h2>Mensajes</h2>
            </div>
            <div class="chat-items">
                <div class="chat-item active">
                    <div class="avatar">JP</div>
                    <div class="chat-info">
                        <span class="user-name">Juan Pérez</span>
                        <p class="last-msg">¿Tienen stock del Galaxy A15?</p>
                    </div>
                </div>
                </div>
        </div>

        <div class="chat-window">
            <div class="chat-header">
                <div class="avatar">JP</div>
                <div class="header-user-info">
                    <h3>Juan Pérez</h3>
                    <span>En línea</span>
                </div>
            </div>
            
            <div class="chat-messages">
                <div class="message received">
                    <p>Hola, buenas tardes. ¿Tienen el Smartphone Galaxy A15 en color azul?</p>
                    <span class="time">10:30 AM</span>
                </div>
                <div class="message sent">
                    <p>¡Hola Juan! Sí, tenemos stock disponible. ¿Te gustaría apartar uno?</p>
                    <span class="time">10:32 AM</span>
                </div>
            </div>

            <div class="chat-input-area">
                <input type="text" placeholder="Escribe un mensaje...">
                <button class="send-btn">
                    <i class="fas fa-paper-plane"></i> Enviar
                </button>
            </div>
        </div>
    </div>
</div>


<?php require_once APP . '/views/inc/footer.php' ?>