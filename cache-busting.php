<?php
/**
 * Plugin Name:       Cache Busting
 * Plugin URI:        https://github.com/marcelovianaandrade/cache-busting
 * Description:       Força a atualização de CSS e JS nos navegadores através de versionamento por query string. Inclui interface administrativa para alterar a versão e visualizar o histórico.
 * Version:           1.0.1
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            Marcelo Viana de Andrade
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cache-busting
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Bloqueia acesso direto
}

class MVA_Cache_Busting {

	const OPTION_VERSION    = 'mva_cb_version';
	const OPTION_UPDATED_AT = 'mva_cb_updated_at';
	const OPTION_UPDATED_BY = 'mva_cb_updated_by';
	const MENU_SLUG         = 'cache-busting';

	public function __construct() {
		// Filtros que aplicam o cache busting
		add_filter( 'style_loader_src',  array( $this, 'cache_bust' ), 999 );
		add_filter( 'script_loader_src', array( $this, 'cache_bust' ), 999 );

		// Interface administrativa
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Link "Configurações" na listagem de plugins
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_settings_link' ) );

		// Ativação
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	/**
	 * Define um valor inicial ao ativar o plugin.
	 */
	public function activate() {
		if ( ! get_option( self::OPTION_VERSION ) ) {
			update_option( self::OPTION_VERSION, date( 'Y.m.d' ) . '.1' );
			update_option( self::OPTION_UPDATED_AT, current_time( 'mysql' ) );
		}
	}

	/**
	 * Aplica o parâmetro ?cv=VERSAO em todos os CSS/JS do próprio site.
	 */
	public function cache_bust( $src ) {
		$versao = get_option( self::OPTION_VERSION );
		if ( ! $versao ) {
			return $src;
		}
		if ( strpos( $src, site_url() ) !== false ) {
			$src = add_query_arg( 'cv', $versao, remove_query_arg( 'cv', $src ) );
		}
		return $src;
	}

	/**
	 * Gera a próxima versão sugerida (data atual + incremento).
	 */
	private function get_next_version() {
		$current = get_option( self::OPTION_VERSION, '' );
		$today   = date( 'Y.m.d' );

		if ( preg_match( '/^(\d{4}\.\d{2}\.\d{2})\.(\d+)$/', $current, $m ) ) {
			if ( $m[1] === $today ) {
				return $today . '.' . ( intval( $m[2] ) + 1 );
			}
		}
		return $today . '.1';
	}

	/**
	 * Adiciona o item de menu em "Configurações".
	 */
	public function add_admin_menu() {
		add_options_page(
			'Cache Busting',
			'Cache Busting',
			'manage_options',
			self::MENU_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	public function add_settings_link( $links ) {
		$url           = admin_url( 'options-general.php?page=' . self::MENU_SLUG );
		$settings_link = '<a href="' . esc_url( $url ) . '">Configurações</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Registra o setting + sanitização.
	 */
	public function register_settings() {
		register_setting(
			'mva_cb_settings',
			self::OPTION_VERSION,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_version' ),
				'default'           => date( 'Y.m.d' ) . '.1',
			)
		);
	}

	/**
	 * Valida o formato AAAA.MM.DD.N. Se válido, registra o timestamp e o autor.
	 */
	public function sanitize_version( $value ) {
		$value   = trim( (string) $value );
		$current = get_option( self::OPTION_VERSION );

		if ( ! preg_match( '/^\d{4}\.\d{2}\.\d{2}\.\d+$/', $value ) ) {
			add_settings_error(
				self::OPTION_VERSION,
				'mva_cb_invalid',
				'Formato inválido. Use AAAA.MM.DD.N (ex: ' . date( 'Y.m.d' ) . '.1).',
				'error'
			);
			return $current;
		}

		if ( $value !== $current ) {
			update_option( self::OPTION_UPDATED_AT, current_time( 'mysql' ) );
			$user = wp_get_current_user();
			update_option( self::OPTION_UPDATED_BY, $user ? $user->display_name : '' );

			add_settings_error(
				self::OPTION_VERSION,
				'mva_cb_updated',
				'Versão atualizada para <strong>' . esc_html( $value ) . '</strong>. O cache dos navegadores será forçado a recarregar.',
				'success'
			);
		}

		return $value;
	}

	/**
	 * Trata o botão de "Atualização Rápida" (incremento automático).
	 */
	private function handle_quick_increment() {
		if (
			isset( $_POST['mva_cb_increment'], $_POST['_wpnonce'] )
			&& current_user_can( 'manage_options' )
			&& wp_verify_nonce( $_POST['_wpnonce'], 'mva_cb_increment_action' )
		) {
			$new_version = $this->get_next_version();
			update_option( self::OPTION_VERSION, $new_version );
			update_option( self::OPTION_UPDATED_AT, current_time( 'mysql' ) );
			$user = wp_get_current_user();
			update_option( self::OPTION_UPDATED_BY, $user ? $user->display_name : '' );

			add_settings_error(
				self::OPTION_VERSION,
				'mva_cb_incremented',
				'✅ Versão incrementada para <strong>' . esc_html( $new_version ) . '</strong>. Os navegadores recarregarão os arquivos.',
				'success'
			);
		}
	}

	/**
	 * Renderiza a tela administrativa.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->handle_quick_increment();

		$current_version = get_option( self::OPTION_VERSION, date( 'Y.m.d' ) . '.1' );
		$updated_at      = get_option( self::OPTION_UPDATED_AT, '' );
		$updated_by      = get_option( self::OPTION_UPDATED_BY, '' );
		$next_version    = $this->get_next_version();
		?>
		<div class="wrap mva-cb-wrap">
			<h1 style="display:flex; align-items:center; gap:10px;">
				<span class="dashicons dashicons-update" style="font-size:30px; width:30px; height:30px;"></span>
				Cache Busting — Forçar Atualização
			</h1>

			<?php settings_errors(); ?>

			<p style="max-width:780px;">
				Este plugin adiciona <code>?cv=VERSAO</code> ao final de todas as URLs de CSS e JS do seu site.
				Sempre que você alterar o número da versão, os navegadores serão forçados a baixar os arquivos novamente,
				em vez de usar a cópia em cache.
			</p>

			<div class="mva-cb-grid">
				<!-- CARD: VERSÃO ATUAL -->
				<div class="mva-cb-card mva-cb-card--current">
					<div class="mva-cb-card__label">
						<span class="dashicons dashicons-tag"></span> Versão Atual
					</div>
					<div class="mva-cb-card__value"><?php echo esc_html( $current_version ); ?></div>
					<div class="mva-cb-card__meta">
						<strong>Última atualização:</strong><br>
						<?php
						if ( $updated_at ) {
							echo esc_html( mysql2date( 'd/m/Y \à\s H:i:s', $updated_at ) );
						} else {
							echo '—';
						}
						?>
						<?php if ( $updated_by ) : ?>
							<br><span style="color:#646970;">por <?php echo esc_html( $updated_by ); ?></span>
						<?php endif; ?>
					</div>
				</div>

				<!-- CARD: ATUALIZAÇÃO RÁPIDA -->
				<div class="mva-cb-card mva-cb-card--quick">
					<div class="mva-cb-card__label">
						<span class="dashicons dashicons-controls-fastforward"></span> Atualização Rápida
					</div>
					<div class="mva-cb-card__value"><?php echo esc_html( $next_version ); ?></div>
					<div class="mva-cb-card__meta">
						Clique no botão abaixo para incrementar a versão automaticamente.
					</div>
					<form method="post" style="margin-top:14px;">
						<?php wp_nonce_field( 'mva_cb_increment_action' ); ?>
						<button
							type="submit"
							name="mva_cb_increment"
							class="button button-primary button-hero"
							onclick="return confirm('Confirma a atualização da versão para <?php echo esc_js( $next_version ); ?>?');"
						>
							<span class="dashicons dashicons-update" style="margin-top:5px;"></span>
							Forçar Atualização Agora
						</button>
					</form>
				</div>
			</div>

			<hr style="margin:30px 0;">

			<h2>Editar Versão Manualmente</h2>
			<p>
				Use o formato <code>AAAA.MM.DD.N</code> — exemplo:
				<code><?php echo esc_html( date( 'Y.m.d' ) ); ?>.1</code>.
				Incremente o último número (<code>.N</code>) para múltiplas atualizações no mesmo dia.
			</p>

			<form method="post" action="options.php">
				<?php settings_fields( 'mva_cb_settings' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( self::OPTION_VERSION ); ?>">Número da versão</label>
						</th>
						<td>
							<input
								type="text"
								id="<?php echo esc_attr( self::OPTION_VERSION ); ?>"
								name="<?php echo esc_attr( self::OPTION_VERSION ); ?>"
								value="<?php echo esc_attr( $current_version ); ?>"
								class="regular-text code"
								pattern="\d{4}\.\d{2}\.\d{2}\.\d+"
								placeholder="<?php echo esc_attr( date( 'Y.m.d' ) . '.1' ); ?>"
								required
							>
							<p class="description">
								Formato: ano.mês.dia.incremento (ex: 2026.05.07.1).
								Apenas dígitos e pontos.
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button( 'Salvar Versão' ); ?>
			</form>

			<hr style="margin:30px 0;">

			<h2>Como funciona</h2>
			<ul style="list-style:disc; padding-left:20px; max-width:780px;">
				<li>O plugin adiciona <code>?cv=<?php echo esc_html( $current_version ); ?></code> ao final de todas as URLs de CSS e JS do seu site.</li>
				<li>Quando você altera o número, todos os navegadores baixam os arquivos novamente em vez de usar o cache.</li>
				<li>Apenas arquivos do próprio site são afetados — URLs externas (Google Fonts, CDNs, etc.) são ignoradas.</li>
				<li>O padrão recomendado é <code>AAAA.MM.DD.N</code>. Incremente o <code>N</code> para múltiplas atualizações no mesmo dia.</li>
			</ul>
		</div>

		<style>
			.mva-cb-wrap .mva-cb-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
				gap: 20px;
				margin-top: 20px;
			}
			.mva-cb-wrap .mva-cb-card {
				background: #fff;
				padding: 22px 24px;
				border-radius: 6px;
				box-shadow: 0 1px 3px rgba(0,0,0,.06);
				border-left: 5px solid #2271b1;
			}
			.mva-cb-wrap .mva-cb-card--quick {
				border-left-color: #00a32a;
			}
			.mva-cb-wrap .mva-cb-card__label {
				font-size: 13px;
				text-transform: uppercase;
				letter-spacing: .5px;
				color: #646970;
				font-weight: 600;
				margin-bottom: 8px;
				display: flex;
				align-items: center;
				gap: 6px;
			}
			.mva-cb-wrap .mva-cb-card__value {
				font-size: 28px;
				font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
				font-weight: 700;
				color: #2271b1;
				margin: 4px 0 12px;
				word-break: break-all;
			}
			.mva-cb-wrap .mva-cb-card--quick .mva-cb-card__value {
				color: #00a32a;
			}
			.mva-cb-wrap .mva-cb-card__meta {
				font-size: 13px;
				color: #1d2327;
				line-height: 1.5;
			}
		</style>
		<?php
	}
}

new MVA_Cache_Busting();
