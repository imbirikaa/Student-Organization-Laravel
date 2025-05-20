import os
import re
from pathlib import Path

# Migration bağımlılıklarını tanımla
dependencies = {
    'create_cities_table': [],
    'create_universities_table': ['create_cities_table'],
    'create_departments_table': ['create_universities_table'],
    'create_users_table': [],
    'create_roles_table': [],
    'create_badges_table': [],
    'create_communities_table': [],
    'create_community_roles_table': ['create_communities_table'],
    'create_faq_table': [],
    'create_events_table': ['create_communities_table'],
    'create_event_sessions_table': ['create_events_table'],
    'create_certificates_table': ['create_events_table', 'create_users_table'],
    'create_session_attendance_table': ['create_event_sessions_table', 'create_users_table'],
    'create_permissions_table': [],
    'create_community_role_permissions_table': ['create_community_roles_table', 'create_permissions_table'],
    'create_community_memberships_table': ['create_communities_table', 'create_users_table', 'create_community_roles_table'],
    'create_community_gallery_table': ['create_communities_table'],
    'create_quizzes_table': ['create_events_table'],
    'create_quiz_questions_table': ['create_quizzes_table'],
    'create_quiz_answers_table': ['create_quiz_questions_table'],
    'create_quiz_submissions_table': ['create_quizzes_table', 'create_users_table'],
    'create_forum_categories_table': [],
    'create_forum_topics_table': ['create_forum_categories_table', 'create_users_table'],
    'create_forum_posts_table': ['create_forum_topics_table', 'create_users_table'],
    'create_topic_likes_table': ['create_forum_topics_table', 'create_users_table'],
    'create_post_likes_table': ['create_forum_posts_table', 'create_users_table'],
    'create_friendships_table': ['create_users_table'],
    'create_messages_table': ['create_chat_rooms_table', 'create_users_table'],
    'create_chat_rooms_table': [],
    'create_chat_room_users_table': ['create_chat_rooms_table', 'create_users_table'],
    'create_notifications_table': ['create_users_table'],
    'create_job_posts_table': ['create_users_table'],
    'create_support_tickets_table': ['create_users_table'],
    'create_support_ticket_messages_table': ['create_support_tickets_table', 'create_users_table'],
    'create_system_logs_table': ['create_users_table'],
    'create_temp_bans_table': ['create_users_table'],
    'create_user_badges_table': ['create_users_table', 'create_badges_table'],
    'create_user_certificates_table': ['create_users_table'],
    'create_user_coupons_table': ['create_users_table', 'create_coupons_table'],
    'create_coupons_table': [],
    'create_user_roles_table': ['create_users_table', 'create_roles_table'],
    'create_personal_access_tokens_table': [],
    'create_profile_visits_table': ['create_users_table'],
}

# Migrations klasör yolu - Windows uyumlu yol
migrations_path = Path(r'C:\xampp\htdocs\kampus_sozluk\database\migrations')

# Migration dosyalarını oku
migration_files = list(migrations_path.glob('*.php'))

# Dosya adlarını ayıkla ve eşleştir
migration_data = {}
for file in migration_files:
    match = re.search(r'\d{4}_\d{2}_\d{2}_\d{6}_(.*)\.php', file.name)
    if match:
        migration_name = match.group(1)
        migration_data[migration_name] = file

# Dosyaları bağımlılık sırasına göre sırala
sorted_migrations = []
visited = set()

def sort_migration(name):
    if name not in visited:
        visited.add(name)
        for dep in dependencies.get(name, []):
            sort_migration(dep)
        if name in migration_data:
            sorted_migrations.append(migration_data[name])

# Bağımlılık sırasına göre migration'ları sırala
for migration in dependencies.keys():
    sort_migration(migration)

# Dosyaları yeniden adlandır
for i, file in enumerate(sorted_migrations, start=1):
    new_name = f"{i:04d}_{file.name.split('_', 1)[-1]}"
    new_path = migrations_path / new_name
    os.rename(file, new_path)
    print(f"✔️ {file.name} → {new_name}")

print("\n✅ Migration sıralama işlemi tamamlandı!")
