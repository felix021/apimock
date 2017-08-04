create database if not exists apimock;
use apimock;

create table api
(
    api_id int primary key auto_increment,
    api_name char(64) not null default '',
    api_desc char(128) not null default '',
    api_result_id int not null default 0 COMMENT "目前选用的result",
    api_created_at DATETIME NOT NULL DEFAULT "1000-01-01 00:00:00" COMMENT '创建时间',
    api_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT '更新时间',
    unique key I_api_name (api_name)
) ENGINE = InnoDB CHARACTER SET utf8mb4;

create table result
(
    result_id int primary key auto_increment,
    result_api_id int not null,
    result_desc   char(128) not null default '',
    result_content text not null default '',
    result_created_at DATETIME NOT NULL DEFAULT "1000-01-01 00:00:00" COMMENT '创建时间',
    result_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT '更新时间',
    CONSTRAINT FK_api_result FOREIGN KEY (`result_api_id`) REFERENCES api (`api_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB CHARACTER SET utf8mb4;

create table batch
(
    batch_id int primary key auto_increment,
    batch_name char(128) not null,
    batch_desc char(255) not null default '',
    batch_created_at DATETIME NOT NULL DEFAULT "1000-01-01 00:00:00" COMMENT '创建时间',
    batch_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT '更新时间',
    unique key I_batch_name (batch_name)
) ENGINE = InnoDB CHARACTER SET utf8mb4;

create table rule
(
    rule_id int primary key auto_increment,
    rule_name char(128) not null default '',
    rule_batch_id int not null,
    rule_api_id int not null,
    rule_result_id int not null,
    rule_created_at DATETIME NOT NULL DEFAULT "1000-01-01 00:00:00" COMMENT '创建时间',
    rule_updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT '更新时间',
    unique key I_rule (rule_batch_id, rule_api_id),
    CONSTRAINT FK_rule_batch FOREIGN KEY (`rule_batch_id`) REFERENCES batch (`batch_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT FK_rule_api FOREIGN KEY (`rule_api_id`) REFERENCES api (`api_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT FK_rule_result FOREIGN KEY (`rule_result_id`) REFERENCES result (`result_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB CHARACTER SET utf8mb4;
