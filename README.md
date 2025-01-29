# Badge Magic LED Badge

**Magically Create Text and Draw Cliparts on LED Name Badges using Bluetooth**

The Badge Magic Android app lets you create moving text and draw clipart for LED name badges. The app provides options to portray names, cliparts, and simple animations on the badges. For the data transfer from the smartphone to the LED badge it uses Bluetooth.

| <!-- -->    | <!-- -->    | <!-- -->    |
|-------------|-------------|-------------|
| <img src="./img/screen-1.jpg" width="288" /> <img src="./img/screen-1-hard.png" width="288" /> | <img src="./img/screen-2.jpg" width="288" /> <img src="./img/screen-2-hard.png" width="288" /> | <img src="./img/screen-3.jpg" width="288" /> <img src="./img/screen-3-hard.png" width="288" /> |


## Android

* [Google Playstore](https://play.google.com/store/apps/details?id=org.fossasia.badgemagic)
* [Fdroid](https://f-droid.org/en/packages/org.fossasia.badgemagic/)

## iOS

* [Badge Magic App on Apple store](https://apps.apple.com/us/app/badge-magic/id6740176888)

## **How to Switch the LED Badge On & Off**

### **Turning the LED Badge On**  
- Press the **top button** on the side of the LED badge once to turn it on.

### **Turning the LED Badge Off**  
- Press the **same button three times** in quick succession to power off the device.

### **Button Press Functions Overview:**
1. **First Press** → Turns the LED badge on.  
2. **Second Press** → Activates **Bluetooth mode**, allowing message transfers.  
3. **Third Press** → Turns the LED badge off.  

---

## **How to Transfer Messages to the LED Badge**

1. **Scan the QR Code**  
   - Locate the **QR code on the back** of the LED badge and scan it using your mobile device.  
   - This will redirect you to the official website to download the app.

2. **Install the Right App**  
   - Choose the compatible app for your device: **Android, iOS, or Python**.

3. **Enable Bluetooth on Your Mobile Device**  
   - There is **no need to manually pair** your phone with the LED badge, as it uses **Bluetooth Low Energy (BLE)** for automatic connection.

4. **Enter Your Desired Message in the App**  
   - Open the app and type in the text you want to display on the LED badge.

5. **Prepare the LED Badge for Transfer**  
   - **Turn on the LED badge** by pressing the top button once.
   - **Press the button again** to activate **Bluetooth transfer mode**—a Bluetooth icon will appear on the display.

6. **Transfer the Message**  
   - In the app, tap **"Transfer" or "Send"** to transmit your message to the LED badge.  
   - The badge will update and display your new message in real time.  

![badgemagic](/img/badgemagic.jpg)

## What is the other button for?

The **second button** on the LED Badge serves multiple functions:  

- **Message Slot Switching:** This button allows users to **cycle through different pre-stored messages** on the badge. The **app is currently being updated** to fully support this feature, and it will be available in an upcoming release. This will enable users to seamlessly switch between multiple custom messages without needing to reconnect to the app.  

- **Brightness Control:** Holding down the **second button for a longer duration** will allow users to **increase or decrease the brightness of the LEDs**.

## Workshop

* [Python Workshop (Google Doc)](https://docs.google.com/document/d/1Ax9lLDBA7hwRKgq2kBHhTh892YbRXS-ZGYeldZXPOwY/edit?tab=t.0)

## Development

* [Badge Magic App Code](https://github.com/fossasia/badgemagic-app): Flutter app for Android, iOS, Linux, macOS, and Windows
* [Python App Code](https://github.com/fossasia/led-name-badge-ls32)
* [Rust App Code](https://github.com/fossasia/badgemagic-rs)
* [Go App Code](https://github.com/fossasia/badgemagic-go)
* [Badge Magic Firmware](https://github.com/fossasia/badgemagic-firmware)

## Hardware

* [Badge Magic Hardware](https://github.com/fossasia/badgemagic-hardware)
* [Badge Magic Case](https://github.com/fossasia/badgemagic-case)

## Web

* [Visit the website here](https://badgemagic.fossasia.org)
