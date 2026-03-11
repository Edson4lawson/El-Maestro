// Utilitaires pour les URLs d'images
export const getImageUrl = (imageUrl) => {
  if (!imageUrl) return ''
  return imageUrl.replace('localhost:8000', 'localhost:8080')
}
