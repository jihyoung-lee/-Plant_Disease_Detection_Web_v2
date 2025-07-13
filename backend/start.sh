echo "✅ Laravel 준비 시작..."

if [ ! -f .env ]; then
  echo "❌ .env 파일이 없습니다. Render 환경변수로 Laravel 설정이 필요합니다."
  exit 1
fi

# Laravel 설정 캐시
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🚀 Laravel 서버 실행 중..."
exec php artisan serve --host=0.0.0.0 --port=8000
