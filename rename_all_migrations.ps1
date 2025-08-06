# Script para renombrar todas las migraciones a formato de 3 dígitos

# Obtener todas las migraciones que empiecen con números
$migrations = Get-ChildItem -Filter "*.php" | Where-Object { $_.Name -match '^(\d+)_' }

foreach ($migration in $migrations) {
    $currentName = $migration.Name
    $match = [regex]::Match($currentName, '^(\d+)_')

    if ($match.Success) {
        $number = $match.Groups[1].Value
        $restOfName = $currentName.Substring($match.Length)

        # Formatear el número a 3 dígitos
        $newNumber = $number.PadLeft(3, '0')
        $newName = $newNumber + $restOfName

        if ($currentName -ne $newName) {
            Write-Host "Renombrando: $currentName -> $newName"
            Rename-Item $currentName $newName
        }
    }
}

Write-Host "Renombrado completado!"
