<?php
/**
 * Template Name: Health Features Dashboard
 * Description: Display all 10 new health features
 */

get_header();
?>

<main id="main" class="site-main shm-features-page">
	<div class="shm-container">
		<?php
		if ( is_user_logged_in() ) :
			shm_theme_user_greeting();
		?>
			
			<div class="shm-features-intro">
				<h1><?php _e( 'Health Features Dashboard', 'smart-health-monitoring' ); ?></h1>
				<p><?php _e( 'Manage your complete health profile with these comprehensive features.', 'smart-health-monitoring' ); ?></p>
			</div>

			<div class="shm-features-grid">
				<div class="shm-feature-card">
					<?php shm_appointments_widget(); ?>
				</div>

				<div class="shm-feature-card">
					<?php shm_medications_widget(); ?>
				</div>

				<div class="shm-feature-card">
					<?php shm_health_goals_widget(); ?>
				</div>

				<div class="shm-feature-card">
					<?php shm_emergency_contacts_widget(); ?>
				</div>

				<div class="shm-feature-card">
					<?php shm_health_journal_widget(); ?>
				</div>

				<div class="shm-feature-card">
					<?php shm_symptom_tracker_widget(); ?>
				</div>

				<div class="shm-feature-card">
					<?php shm_water_tracker_widget(); ?>
				</div>

				<div class="shm-feature-card">
					<?php shm_sleep_tracker_widget(); ?>
				</div>

				<div class="shm-feature-card">
					<?php shm_bmi_calculator_widget(); ?>
				</div>

				<div class="shm-feature-card">
					<?php shm_document_manager_widget(); ?>
				</div>
			</div>

		<?php else : ?>
			<div class="shm-login-notice">
				<h2><?php _e( 'Please Log In', 'smart-health-monitoring' ); ?></h2>
				<p><?php _e( 'You need to be logged in to access health features.', 'smart-health-monitoring' ); ?></p>
				<a href="<?php echo wp_login_url( get_permalink() ); ?>" class="shm-btn shm-btn-primary">
					<?php _e( 'Log In', 'smart-health-monitoring' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</main>

<style>
.shm-features-page {
	padding: 2rem 0;
}

.shm-features-intro {
	text-align: center;
	margin-bottom: 2rem;
}

.shm-features-intro h1 {
	color: var(--color-primary, #0ea5e9);
	margin-bottom: 0.5rem;
}

.shm-features-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
	gap: 2rem;
	margin-top: 2rem;
}

.shm-feature-card {
	background: #fff;
	border-radius: 8px;
	padding: 1.5rem;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.shm-feature-card:hover {
	transform: translateY(-4px);
	box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.shm-feature-card h3 {
	color: #1e293b;
	margin-bottom: 1rem;
	font-size: 1.25rem;
	border-bottom: 2px solid #e2e8f0;
	padding-bottom: 0.5rem;
}

.shm-btn {
	display: inline-block;
	padding: 0.5rem 1rem;
	background: #0ea5e9;
	color: #fff;
	border: none;
	border-radius: 4px;
	cursor: pointer;
	transition: background 0.3s ease;
	text-decoration: none;
	font-weight: 500;
}

.shm-btn:hover {
	background: #0284c7;
}

.shm-btn-primary {
	background: #0ea5e9;
}

.shm-btn-primary:hover {
	background: #0284c7;
}

.shm-btn-danger {
	background: #ef4444;
}

.shm-btn-danger:hover {
	background: #dc2626;
}

.shm-btn-warning {
	background: #f59e0b;
}

.shm-btn-warning:hover {
	background: #d97706;
}

.shm-btn-small {
	padding: 0.25rem 0.5rem;
	font-size: 0.875rem;
}

.shm-modal {
	display: none;
	position: fixed;
	z-index: 1000;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	overflow: auto;
	background-color: rgba(0,0,0,0.5);
}

.shm-modal-content {
	background-color: #fff;
	margin: 5% auto;
	padding: 2rem;
	border-radius: 8px;
	max-width: 600px;
	position: relative;
}

.shm-modal-close {
	position: absolute;
	right: 1rem;
	top: 1rem;
	font-size: 2rem;
	font-weight: bold;
	color: #aaa;
	cursor: pointer;
}

.shm-modal-close:hover {
	color: #000;
}

.form-group {
	margin-bottom: 1rem;
}

.form-group label {
	display: block;
	margin-bottom: 0.25rem;
	font-weight: 500;
	color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
	width: 100%;
	padding: 0.5rem;
	border: 1px solid #d1d5db;
	border-radius: 4px;
	font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
	outline: none;
	border-color: #0ea5e9;
}

.appointments-list,
.medications-list,
.contacts-list,
.journal-entries,
.symptoms-list,
.sleep-records {
	margin-top: 1rem;
}

.appointments-list li,
.medications-list li {
	padding: 0.75rem;
	background: #f9fafb;
	border-left: 3px solid #0ea5e9;
	margin-bottom: 0.5rem;
	border-radius: 4px;
}

.contact-card,
.journal-entry,
.symptom-card,
.sleep-record {
	padding: 1rem;
	background: #f9fafb;
	border-radius: 4px;
	margin-bottom: 0.75rem;
}

.contact-card h4,
.journal-entry h4,
.symptom-card h4,
.sleep-record h4 {
	margin: 0 0 0.5rem 0;
	color: #1e293b;
}

.primary-contact {
	border: 2px solid #10b981;
}

.badge {
	display: inline-block;
	padding: 0.25rem 0.5rem;
	background: #10b981;
	color: white;
	border-radius: 4px;
	font-size: 0.75rem;
	margin-left: 0.5rem;
}

.goals-grid {
	display: grid;
	gap: 1rem;
	margin-top: 1rem;
}

.goal-card {
	padding: 1rem;
	background: #f9fafb;
	border-radius: 4px;
}

.progress-bar {
	height: 20px;
	background: #e5e7eb;
	border-radius: 10px;
	overflow: hidden;
	margin: 0.5rem 0;
}

.progress-fill {
	height: 100%;
	background: linear-gradient(90deg, #10b981, #059669);
	transition: width 0.3s ease;
}

.water-stats {
	display: flex;
	align-items: center;
	gap: 1rem;
	margin-bottom: 1rem;
}

.water-icon {
	font-size: 3rem;
}

.water-quick-add {
	display: flex;
	gap: 0.5rem;
	margin: 1rem 0;
	flex-wrap: wrap;
}

.water-btn {
	padding: 0.5rem 1rem;
	background: #06b6d4;
	color: white;
	border: none;
	border-radius: 4px;
	cursor: pointer;
}

.water-btn:hover {
	background: #0891b2;
}

.progress-bar-water {
	height: 30px;
	background: #e0f2fe;
	border-radius: 15px;
	overflow: hidden;
	margin: 1rem 0;
}

.progress-fill-water {
	height: 100%;
	background: linear-gradient(90deg, #06b6d4, #0891b2);
	transition: width 0.3s ease;
}

.bmi-current {
	text-align: center;
	padding: 1rem;
	background: #f0f9ff;
	border-radius: 8px;
	margin-bottom: 1rem;
}

.bmi-value h2 {
	font-size: 3rem;
	margin: 0.5rem 0;
	color: #0ea5e9;
}

.bmi-records-table {
	width: 100%;
	border-collapse: collapse;
	margin-top: 1rem;
}

.bmi-records-table th,
.bmi-records-table td {
	padding: 0.5rem;
	text-align: left;
	border-bottom: 1px solid #e5e7eb;
}

.bmi-records-table th {
	background: #f9fafb;
	font-weight: 600;
}

.bmi-info {
	margin-top: 1rem;
	padding: 1rem;
	background: #fef3c7;
	border-radius: 4px;
}

.bmi-info ul {
	list-style: none;
	padding: 0;
}

.bmi-info li {
	padding: 0.25rem 0;
}

.documents-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
	gap: 1rem;
	margin-top: 1rem;
}

.document-card {
	padding: 1rem;
	background: #f9fafb;
	border-radius: 4px;
	text-align: center;
}

.doc-icon {
	font-size: 3rem;
	margin-bottom: 0.5rem;
}

.document-card h4 {
	margin: 0.5rem 0;
	font-size: 1rem;
}

.shm-login-notice {
	text-align: center;
	padding: 3rem;
	background: #f9fafb;
	border-radius: 8px;
}

@media (max-width: 768px) {
	.shm-features-grid {
		grid-template-columns: 1fr;
	}
	
	.documents-grid {
		grid-template-columns: 1fr;
	}
}

body.dark-mode .shm-feature-card,
body.dark-mode .shm-modal-content {
	background: #1e293b;
	color: #e2e8f0;
}

body.dark-mode .shm-feature-card h3 {
	color: #f1f5f9;
	border-bottom-color: #334155;
}

body.dark-mode .form-group input,
body.dark-mode .form-group select,
body.dark-mode .form-group textarea {
	background: #0f172a;
	color: #e2e8f0;
	border-color: #334155;
}

body.dark-mode .contact-card,
body.dark-mode .journal-entry,
body.dark-mode .symptom-card,
body.dark-mode .sleep-record,
body.dark-mode .goal-card,
body.dark-mode .document-card {
	background: #0f172a;
	color: #e2e8f0;
}
</style>

<?php
get_footer();
