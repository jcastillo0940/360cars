import os
import requests
import time

logos = {
    'land-rover': 'https://pngimg.com/uploads/land_rover/land_rover_PNG38.png',
    'lexus': 'https://www.carlogos.org/logo/Lexus-logo-symbol-640x480.png',
    'dodge': 'https://www.carlogos.org/logo/Dodge-logo-2010-640x480.png',
    'isuzu': 'https://logos-download.com/wp-content/uploads/2016/06/Isuzu_logo_red.png',
    'chery': 'https://logos-download.com/wp-content/uploads/2016/09/Chery_logo.png',
    'byd': 'https://logos-download.com/wp-content/uploads/2016/11/BYD_logo_logotype.png',
    'geely': 'https://logos-download.com/wp-content/uploads/2016/09/Geely_logo_black.png',
    'jac': 'https://logos-download.com/wp-content/uploads/2016/09/JAC_Motors_logo.png',
    'baic': 'https://logos-download.com/wp-content/uploads/2016/11/BAIC_logo.png',
    'changan': 'https://logos-download.com/wp-content/uploads/2016/09/Changan_Auto_logo.png',
    'dongfeng': 'https://logos-download.com/wp-content/uploads/2016/09/Dongfeng_Motor_logo.png',
    'faw': 'https://logos-download.com/wp-content/uploads/2016/09/FAW_logo.png',
    'foton': 'https://logos-download.com/wp-content/uploads/2016/09/Foton_logo.png',
    'great-wall': 'https://logos-download.com/wp-content/uploads/2016/09/Great_Wall_Motor_logo.png',
    'gwm': 'https://logos-download.com/wp-content/uploads/2016/09/Great_Wall_Motor_logo.png',
    'haval': 'https://logos-download.com/wp-content/uploads/2016/09/Haval_logo.png',
    'jetour': 'https://logos-download.com/wp-content/uploads/2021/04/Jetour_Logo.png',
    'maxus': 'https://logos-download.com/wp-content/uploads/2021/01/Maxus_Logo.png',
    'ssangyong': 'https://logos-download.com/wp-content/uploads/2016/03/SsangYong_logo.png',
    'omoda': 'https://logos-download.com/wp-content/uploads/2023/11/Omoda_Logo.png',
    'jaecoo': 'https://logos-download.com/wp-content/uploads/2023/11/Jaecoo_Logo.png',
    'dfsk': 'https://logos-download.com/wp-content/uploads/2016/10/DFSK_logo.png',
}

base_path = r'e:\apps\360Cars\public\logos'
os.makedirs(base_path, exist_ok=True)

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
}

for slug, url in logos.items():
    try:
        print(f"Downloading {slug}...")
        response = requests.get(url, headers=headers, timeout=15)
        if response.status_code == 200:
            with open(os.path.join(base_path, f"{slug}.png"), 'wb') as f:
                f.write(response.content)
            print(f"Saved {slug}.png")
        else:
            print(f"Failed to download {slug}: Status {response.status_code}")
        time.sleep(1)
    except Exception as e:
        print(f"Error downloading {slug}: {e}")
