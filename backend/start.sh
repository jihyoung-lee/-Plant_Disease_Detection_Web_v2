echo "✅ Laravel 준비 시작..."

# .env 없이도 Render 환경변수 사용하도록 artisan 실행
if [ ! -f .env ]; then
  echo "⚠️ .env 파일이 없지만, Render 환경변수로 계속 진행합니다."
fi

# Laravel 설정 캐시
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true


echo "🚀 Laravel 서버 실행 중..."
exec php artisan serve --host=0.0.0.0 --port=8000
