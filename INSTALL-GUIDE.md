# DNIWOO - Guía de Instalación Rápida

## Problema: Duplicación de plugins al descargar desde GitHub

Cuando descargas desde GitHub, se crea una carpeta `dniwoo-main` que causa duplicación. Aquí tienes las soluciones:

## Solución 1: Instalación Manual Correcta

### Pasos:

1. **Descarga** desde GitHub: https://github.com/replantadev/dniwoo
2. **Extrae** el archivo ZIP
3. **Renombra** la carpeta de `dniwoo-main` a `dniwoo`
4. **Sube** la carpeta `dniwoo` a `/wp-content/plugins/`
5. **Activa** el plugin desde WordPress admin

### Comando rápido (Linux/Mac):
```bash
cd wp-content/plugins
wget https://github.com/replantadev/dniwoo/archive/refs/heads/main.zip
unzip main.zip
mv dniwoo-main dniwoo
rm main.zip
```

### PowerShell (Windows):
```powershell
cd wp-content\plugins
Invoke-WebRequest -Uri "https://github.com/replantadev/dniwoo/archive/refs/heads/main.zip" -OutFile "dniwoo.zip"
Expand-Archive -Path "dniwoo.zip" -DestinationPath "."
Move-Item "dniwoo-main" "dniwoo"
Remove-Item "dniwoo.zip"
```

## Solución 2: Usar ZIP Pre-compilado

1. Descarga el ZIP preparado: [dniwoo-v1.0.0.zip]
2. Sube directamente via WordPress admin: **Plugins > Añadir nuevo > Subir plugin**

## Solución 3: Usar Scripts de Instalación

### Linux/Mac:
```bash
curl -O https://raw.githubusercontent.com/replantadev/dniwoo/main/install.sh
chmod +x install.sh
./install.sh
```

### Windows:
```cmd
curl -O https://raw.githubusercontent.com/replantadev/dniwoo/main/install.bat
install.bat
```

## Verificación

Después de la instalación, deberías ver en WordPress admin:

✅ **DNIWOO - DNI/NIF for WooCommerce**
- Por Replanta
- Versión 1.0.0
- Professional DNI/NIF field for WooCommerce checkout

❌ **NO deberías ver duplicados o nombres como:**
- "dniwoo-main"
- "WooCommerce Campo DNI/NIF" (plugin antiguo)

## Solución de Problemas

### Si ves duplicados:
1. Desactiva todos los plugins DNI/NIF
2. Elimina las carpetas incorrectas
3. Instala solo el DNIWOO oficial

### Si el nombre aparece mal:
- Verifica que la carpeta se llame exactamente `dniwoo`
- No debe contener `-main` o números de versión

### Estructura correcta:
```
wp-content/plugins/dniwoo/
├── dniwoo.php          (archivo principal)
├── includes/           (clases PHP)
├── assets/            (CSS/JS)
├── README.md
└── otros archivos...
```

## Soporte

- **GitHub**: https://github.com/replantadev/dniwoo/issues
- **Email**: info@replanta.dev
- **Web**: https://replanta.net
