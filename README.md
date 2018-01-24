# MageProfis_SecureAdmin
Add "noindex,nofollow" Meta-Tag to Magento Backend Login.

### Ip Whitelist + Auth
Allow to double secure the admin login with a HTTP-Auth prompt.
Add a IP Whitelist to prevent the HTTP-Auth for sertain IPs.

Create two files in the magento root folder namend "secureadmin_ip.txt" and "secureadmin_auth.txt".
Add your allowed ips line seperated into ""secureadmin_ip.txt".
Add your users for the HTTP-Auth line seperated into "secureadmin_auth.txt" with the format "name:password" e.g. "admin:admin123".