// Utilitaires de résolution d'URLs d'images pour El Maestro
export const getImageUrl = (imageUrl) => {
  if (!imageUrl) return ''
  
  const trimmed = imageUrl.trim()
  if (trimmed.startsWith('http://') || trimmed.startsWith('https://') || trimmed.startsWith('data:') || trimmed.startsWith('blob:')) {
    try {
      return encodeURI(trimmed)
    } catch (e) {
      return trimmed
    }
  }

  // Si c'est un nom de fichier relatif
  const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'
  return `${apiBase}/?file=${encodeURIComponent(trimmed)}`
}
